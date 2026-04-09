<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PriceMaths;
use Level44\Base;
use Level44\Product;


/**
 *
 * This file modifies result for every request (including AJAX).
 * Use it to edit output result for "{{ mustache }}" templates.
 *
 * @var array $result
 */

$mobileColumns = isset($this->arParams['COLUMNS_LIST_MOBILE'])
	? $this->arParams['COLUMNS_LIST_MOBILE']
	: $this->arParams['COLUMNS_LIST'];
$mobileColumns = array_fill_keys($mobileColumns, true);

$result['BASKET_ITEM_RENDER_DATA'] = array();

$totalQuantity = 0;

$sumPriceDollar = 0;
$oldSumPrice = 0;
$oldSumPriceDollar = 0;

$basketProductIds = [];

foreach ($this->basketItems as $item) {
    $basketProductIds[] = (int)$item["PRODUCT_ID"];
}

$productList = \CCatalogSKU::getProductList($basketProductIds);

$productIds = [];
foreach ($productList as $offerId => $product) {
    $productIds[$offerId] = $product["ID"];
}

$productsData = $productIds;

$obProduct = new Product();
$ecommerceData = $obProduct->getEcommerceData(array_values($productsData));
$rsProductsData = \CIBlockElement::GetList(
    [],
    [
        "ID" => array_values($productsData),
        "IBLOCK_ID" => Base::CATALOG_IBLOCK_ID
    ],
    false,
    false,
    [
        "ID",
        "IBLOCK_ID",
        "PROPERTY_PRICE_DOLLAR"
    ]
);

$productsDataExt = [];
while ($productData = $rsProductsData->GetNext()) {
    $productsDataExt[$productData["ID"]] = $productData;
}

foreach ($productsData as $key => &$productsDataItem) {
    $productsDataItem = $productsDataExt[$productsDataItem];
}
unset($productsDataItem);

$productsData = array_filter($productsData);

$products = array_map(function ($productId) {
    return [
        "ID" => $productId
    ];
}, $productIds);

Base::setColorOffers($products);

$arProductsLoc = [];

if (!empty($basketProductIds)) {
    $resProduct = \CIBlockElement::GetList(
        [],
        [
            "=ID" => $basketProductIds
        ],
        false,
        false,
        [
            "ID",
            "PROPERTY_NAME_EN"
        ]
    );


    while ($product = $resProduct->GetNext()) {
        $arProductsLoc[$product["ID"]]["NAME_EN"] = $product["PROPERTY_NAME_EN_VALUE"];
    }
}

// Подготавливаем массив товаров для applyDiscounts
$basketItemsForDiscounts = [];
foreach ($this->basketItems as $row) {
    $basketItemsForDiscounts[] = [
        'ID' => $row['ID'],
        'PRODUCT_ID' => $row['PRODUCT_ID'],
        'PRICE' => $row['PRICE'],
        'BASE_PRICE' => $row['BASE_PRICE'] ?? $row['FULL_PRICE'] ?? $row['PRICE'],
        'SUM_BASE' => $row['SUM_FULL_PRICE'] ?? ($row['BASE_PRICE'] ?? $row['FULL_PRICE'] ?? $row['PRICE']) * $row['QUANTITY'],
        'SUM_NUM' => $row['SUM_VALUE'],
        'QUANTITY' => $row['QUANTITY'],
        'CURRENCY' => $row['CURRENCY'],
        'CURRENCY_ID' => $row['CURRENCY'],
        'oldPrice' => null, // Будет установлено позже из ecommerceData
        'PRICE_FORMATED' => $row['PRICE_FORMATED'],
        'SUM' => $row['SUM'],
    ];
}

// Применяем дополнительные скидки для товаров категории sale
if (!empty($basketItemsForDiscounts) && \Bitrix\Main\Loader::includeModule('iblock')) {
    // Сначала получаем oldPrice для всех товаров
    foreach ($basketItemsForDiscounts as &$item) {
        $parentProductId = $productIds[$item['PRODUCT_ID']] ?? null;
        if ($parentProductId && isset($ecommerceData[$parentProductId]['prices']['oldPrice'])) {
            $item['oldPrice'] = $ecommerceData[$parentProductId]['prices']['oldPrice'] * $item['QUANTITY'];
        }
    }
    unset($item);
    
    // Применяем дополнительные скидки
    $basketItemsForDiscounts = \Level44\Event\SaleCategoryDiscountHandler::applyDiscounts($basketItemsForDiscounts);
    
    // Создаем карту скидок для быстрого поиска
    $discountMap = [];
    foreach ($basketItemsForDiscounts as $discItem) {
        $discountMap[$discItem['ID']] = $discItem;
    }
}

foreach ($this->basketItems as $row)
{
	$rowData = array(
		'ID' => $row['ID'],
		'COLOR' => $products[$row['PRODUCT_ID']],
		'PRODUCT_ID' => $row['PRODUCT_ID'],
		'NAME' => isset($row['~NAME']) ? $row['~NAME'] : $row['NAME'],
		'QUANTITY' => $row['QUANTITY'],
		'PROPS' => $row['PROPS'],
		'PROPS_ALL' => $row['PROPS_ALL'],
		'HASH' => $row['HASH'],
		'SORT' => $row['SORT'],
		'DETAIL_PAGE_URL' => $row['DETAIL_PAGE_URL'],
		'CURRENCY' => $row['CURRENCY'],
		'DISCOUNT_PRICE_PERCENT' => $row['DISCOUNT_PRICE_PERCENT'],
		'DISCOUNT_PRICE_PERCENT_FORMATED' => $row['DISCOUNT_PRICE_PERCENT_FORMATED'],
		'SHOW_DISCOUNT_PRICE' => (float)$row['DISCOUNT_PRICE'] > 0,
		'PRICE' => $row['PRICE'],
		'PRICE_FORMATED' => $row['PRICE_FORMATED'],
		'FULL_PRICE' => $row['FULL_PRICE'],
		'FULL_PRICE_FORMATED' => $row['FULL_PRICE_FORMATED'],
		'DISCOUNT_PRICE' => $row['DISCOUNT_PRICE'],
		'DISCOUNT_PRICE_FORMATED' => $row['DISCOUNT_PRICE_FORMATED'],
		'SUM_PRICE' => $row['SUM_VALUE'],
		'SUM_PRICE_FORMATED' => $row['SUM'],
		'SUM_FULL_PRICE' => $row['SUM_FULL_PRICE'],
		'SUM_FULL_PRICE_FORMATED' => $row['SUM_FULL_PRICE_FORMATED'],
		'SUM_DISCOUNT_PRICE' => $row['SUM_DISCOUNT_PRICE'],
		'SUM_DISCOUNT_PRICE_FORMATED' => $row['SUM_DISCOUNT_PRICE_FORMATED'],
		'MEASURE_RATIO' => isset($row['MEASURE_RATIO']) ? $row['MEASURE_RATIO'] : 1,
		'MEASURE_TEXT' => $row['MEASURE_TEXT'],
		'AVAILABLE_QUANTITY' => $row['AVAILABLE_QUANTITY'],
		'CHECK_MAX_QUANTITY' => $row['CHECK_MAX_QUANTITY'],
		'MODULE' => $row['MODULE'],
		'PRODUCT_PROVIDER_CLASS' => $row['PRODUCT_PROVIDER_CLASS'],
		'NOT_AVAILABLE' => $row['NOT_AVAILABLE'] === true,
		'DELAYED' => $row['DELAY'] === 'Y',
		'SKU_BLOCK_LIST' => array(),
		'COLUMN_LIST' => array(),
		'SHOW_LABEL' => false,
		'LABEL_VALUES' => array(),
		'BRAND' => isset($row[$this->arParams['BRAND_PROPERTY'].'_VALUE'])
			? $row[$this->arParams['BRAND_PROPERTY'].'_VALUE']
			: '',
	);
	
	// Добавляем данные о дополнительных скидках, если они есть
	if (!empty($discountMap[$row['ID']])) {
		$discItem = $discountMap[$row['ID']];
		if (!empty($discItem['SHOW_THREE_PRICES'])) {
			$rowData['SHOW_THREE_PRICES'] = true;
			$rowData['ORIGINAL_PRICE_FORMATED'] = $discItem['SUM_ORIGINAL_PRICE_FORMATED'] ?? $rowData['SUM_FULL_PRICE_FORMATED'];
			$rowData['PRICE_BEFORE_ADDITIONAL_DISCOUNT_FORMATED'] = $discItem['SUM_PRICE_BEFORE_ADDITIONAL_DISCOUNT_FORMATED'] ?? $rowData['SUM_PRICE_FORMATED'];
			$rowData['PRICE_AFTER_ADDITIONAL_DISCOUNT_FORMATED'] = $discItem['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT_FORMATED'] ?? $rowData['SUM_PRICE_FORMATED'];
			
			// Сохраняем числовые значения для пересчета итоговой суммы
			if (isset($discItem['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT'])) {
				$rowData['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT'] = $discItem['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT'];
			}
			if (isset($discItem['SUM_ORIGINAL_PRICE'])) {
				$rowData['SUM_ORIGINAL_PRICE'] = $discItem['SUM_ORIGINAL_PRICE'];
			}
			
			if (!empty($discItem['ADDITIONAL_DISCOUNT_PERCENT'])) {
				$rowData['ADDITIONAL_DISCOUNT_PERCENT'] = $discItem['ADDITIONAL_DISCOUNT_PERCENT'];
			}
		}
	}


    $rowData["NAME"] = Base::getMultiLang(
        $rowData["NAME"],
        $arProductsLoc[$rowData["PRODUCT_ID"]]["NAME_EN"]
    );

	// show price including ratio
	if ($rowData['MEASURE_RATIO'] != 1)
	{
		$price = PriceMaths::roundPrecision($rowData['PRICE'] * $rowData['MEASURE_RATIO']);
		if ($price != $rowData['PRICE'])
		{
			$rowData['PRICE'] = $price;
			$rowData['PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($price, $rowData['CURRENCY'], true);
		}

		$fullPrice = PriceMaths::roundPrecision($rowData['FULL_PRICE'] * $rowData['MEASURE_RATIO']);
		if ($fullPrice != $rowData['FULL_PRICE'])
		{
			$rowData['FULL_PRICE'] = $fullPrice;
			$rowData['FULL_PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($fullPrice, $rowData['CURRENCY'], true);
		}

		$discountPrice = PriceMaths::roundPrecision($rowData['DISCOUNT_PRICE'] * $rowData['MEASURE_RATIO']);
		if ($discountPrice != $rowData['DISCOUNT_PRICE'])
		{
			$rowData['DISCOUNT_PRICE'] = $discountPrice;
			$rowData['DISCOUNT_PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($discountPrice, $rowData['CURRENCY'], true);
		}
	}

	$rowData['SHOW_PRICE_FOR'] = (float)$rowData['QUANTITY'] !== (float)$rowData['MEASURE_RATIO'];

	$hideDetailPicture = false;

	if (!empty($row['PREVIEW_PICTURE_SRC']))
	{
		$rowData['IMAGE_URL'] = $row['PREVIEW_PICTURE_SRC'];
	}
	elseif (!empty($row['DETAIL_PICTURE_SRC']))
	{
		$hideDetailPicture = true;
		$rowData['IMAGE_URL'] = $row['DETAIL_PICTURE_SRC'];
	}

	if (!empty($row['SKU_DATA']))
	{
		$propMap = array();

		foreach($row['PROPS'] as $prop)
		{
			$propMap[$prop['CODE']] = !empty($prop['~VALUE']) ? $prop['~VALUE'] : $prop['VALUE'];
		}

		$notSelectable = true;

		foreach ($row['SKU_DATA'] as $skuBlock)
		{
			$skuBlockData = array(
				'ID' => $skuBlock['ID'],
				'CODE' => $skuBlock['CODE'],
				'NAME' => $skuBlock['NAME']
			);

			$isSkuSelected = false;
			$isImageProperty = false;

			if (count($skuBlock['VALUES']) > 1)
			{
				$notSelectable = false;
			}

			foreach ($skuBlock['VALUES'] as $skuItem)
			{
				if ($skuBlock['TYPE'] === 'S' && $skuBlock['USER_TYPE'] === 'directory')
				{
					$valueId = $skuItem['XML_ID'];
				}
				elseif ($skuBlock['TYPE'] === 'E')
				{
					$valueId = $skuItem['ID'];
				}
				else
				{
					$valueId = $skuItem['NAME'];
				}

				$skuValue = array(
					'ID' => $skuItem['ID'],
					'NAME' => $skuItem['NAME'],
					'SORT' => $skuItem['SORT'],
					'PICT' => !empty($skuItem['PICT']) ? $skuItem['PICT']['SRC'] : false,
					'XML_ID' => !empty($skuItem['XML_ID']) ? $skuItem['XML_ID'] : false,
					'VALUE_ID' => $valueId,
					'PROP_ID' => $skuBlock['ID'],
					'PROP_CODE' => $skuBlock['CODE']
				);

				if (
					!empty($propMap[$skuBlockData['CODE']])
					&& ($propMap[$skuBlockData['CODE']] == $skuItem['NAME'] || $propMap[$skuBlockData['CODE']] == $skuItem['XML_ID'])
				)
				{
					$skuValue['SELECTED'] = true;
					$isSkuSelected = true;
				}

				$skuBlockData['SKU_VALUES_LIST'][] = $skuValue;
				$isImageProperty = $isImageProperty || !empty($skuItem['PICT']);
			}

			if (!$isSkuSelected && !empty($skuBlockData['SKU_VALUES_LIST'][0]))
			{
				$skuBlockData['SKU_VALUES_LIST'][0]['SELECTED'] = true;
			}

			$skuBlockData['IS_IMAGE'] = $isImageProperty;

			$rowData['SKU_BLOCK_LIST'][] = $skuBlockData;
		}
	}

    $rowData["PRICE_DOLLAR"] = (int)$productsData[$rowData["PRODUCT_ID"]]["PROPERTY_PRICE_DOLLAR_VALUE"];
    $itemPriceDollar = 0;

    if ($rowData["PRICE_DOLLAR"] <= 0) {
        $itemPriceDollar = Base::getDollarPrice(
            $rowData["PRICE"],
            null,
            true
        );
    } else {
        $itemPriceDollar = $rowData["PRICE_DOLLAR"];
    }

    $sumPriceDollar = $sumPriceDollar + ($itemPriceDollar * $rowData["QUANTITY"]);
    $rowData["PRICE_DOLLAR"] = Base::getDollarPrice($rowData["PRICE"], $rowData["PRICE_DOLLAR"]);

    $rowData = array_merge($rowData, (array)$ecommerceData[$productsData[$rowData["PRODUCT_ID"]]["ID"]]["prices"]);
    $rowData["showOldPrice"] = !empty($rowData["oldPrice"]);
    $rowData["oldPriceFormat"] = CCurrencyLang::CurrencyFormat($rowData["oldPrice"], "RUB");
    $rowData["oldPriceDollarFormat"] = Base::formatDollar($rowData["oldPriceDollar"]);

    $rowData["oldPrice"] = $rowData["oldPrice"] * $rowData["QUANTITY"];
    $rowData["oldPriceDollar"] = $rowData["oldPriceDollar"] * $rowData["QUANTITY"];

    if (empty($rowData["oldPrice"])) {
        $oldSumPrice += $rowData["SUM_PRICE"];
        $oldSumPriceDollar += $itemPriceDollar;
    } else {
        $oldSumPrice += $rowData["oldPrice"];
        $oldSumPriceDollar += $rowData["oldPriceDollar"];
    }
    $rowData["SELECT_PROP"] = [];

    foreach ($rowData['SKU_BLOCK_LIST'] as $prop) {
        $propValue = reset(array_filter($prop["SKU_VALUES_LIST"],
            function ($item) {
                return $item["SELECTED"] === true;
            }));

        $propValue["PROP_NAME"] = $prop["NAME"];
        if (in_array($prop["CODE"], ["SIZE_REF"])) {
            $rowData["SELECT_PROP"][$prop["CODE"]] = [
                "NAME" => $propValue["PROP_NAME"],
                "VALUE" => $propValue["NAME"],
            ];
        }
    }

    if (!empty($rowData["COLOR"])) {
        $rowData["SELECT_PROP"]["COLOR_REF"] = [
            "NAME" => "",
            "VALUE" => $rowData["COLOR"]["COLOR_NAME"]
        ];
    }

	if ($row['NOT_AVAILABLE'] && false)
	{
		foreach ($rowData['SKU_BLOCK_LIST'] as $blockKey => $skuBlock)
		{
			if (!empty($skuBlock['SKU_VALUES_LIST']))
			{
				if ($notSelectable)
				{
					foreach ($skuBlock['SKU_VALUES_LIST'] as $valueKey => $skuValue)
					{
						$rowData['SKU_BLOCK_LIST'][$blockKey]['SKU_VALUES_LIST'][0]['NOT_AVAILABLE_OFFER'] = true;
					}
				}
				elseif (!isset($rowData['SKU_BLOCK_LIST'][$blockKey + 1]))
				{
					foreach ($skuBlock['SKU_VALUES_LIST'] as $valueKey => $skuValue)
					{
						if ($skuValue['SELECTED'])
						{
							$rowData['SKU_BLOCK_LIST'][$blockKey]['SKU_VALUES_LIST'][$valueKey]['NOT_AVAILABLE_OFFER'] = true;
						}
					}
				}
			}
		}
	}

	if (!empty($result['GRID']['HEADERS']) && is_array($result['GRID']['HEADERS']))
	{
		$skipHeaders = [
			'NAME' => true,
			'QUANTITY' => true,
			'PRICE' => true,
			'PREVIEW_PICTURE' => true,
			'SUM' => true,
			'PROPS' => true,
			'DELETE' => true,
			'DELAY' => true,
		];

		foreach ($result['GRID']['HEADERS'] as &$value)
		{
			if (
				empty($value['id'])
				|| isset($skipHeaders[$value['id']])
				|| ($hideDetailPicture && $value['id'] === 'DETAIL_PICTURE'))
			{
				continue;
			}

			if ($value['id'] === 'DETAIL_PICTURE')
			{
				$value['name'] = Loc::getMessage('SBB_DETAIL_PICTURE_NAME');

				if (!empty($row['DETAIL_PICTURE_SRC']))
				{
					$rowData['COLUMN_LIST'][] = array(
						'CODE' => $value['id'],
						'NAME' => $value['name'],
						'VALUE' => array(
							array(
								'IMAGE_SRC' => $row['DETAIL_PICTURE_SRC'],
								'IMAGE_SRC_2X' => $row['DETAIL_PICTURE_SRC_2X'],
								'IMAGE_SRC_ORIGINAL' => $row['DETAIL_PICTURE_SRC_ORIGINAL'],
								'INDEX' => 0
							)
						),
						'IS_IMAGE' => true,
						'HIDE_MOBILE' => !isset($mobileColumns[$value['id']])
					);
				}
			}
			elseif ($value['id'] === 'PREVIEW_TEXT')
			{
				$value['name'] = Loc::getMessage('SBB_PREVIEW_TEXT_NAME');

				if ($row['PREVIEW_TEXT_TYPE'] === 'text' && !empty($row['PREVIEW_TEXT']))
				{
					$rowData['COLUMN_LIST'][] = array(
						'CODE' => $value['id'],
						'NAME' => $value['name'],
						'VALUE' => $row['PREVIEW_TEXT'],
						'IS_TEXT' => true,
						'HIDE_MOBILE' => !isset($mobileColumns[$value['id']])
					);
				}
			}
			elseif ($value['id'] === 'TYPE')
			{
				$value['name'] = Loc::getMessage('SBB_PRICE_TYPE_NAME');

				if (!empty($row['NOTES']))
				{
					$rowData['COLUMN_LIST'][] = array(
						'CODE' => $value['id'],
						'NAME' => $value['name'],
						'VALUE' => isset($row['~NOTES']) ? $row['~NOTES'] : $row['NOTES'],
						'IS_TEXT' => true,
						'HIDE_MOBILE' => !isset($mobileColumns[$value['id']])
					);
				}
			}
			elseif ($value['id'] === 'DISCOUNT')
			{
				$value['name'] = Loc::getMessage('SBB_DISCOUNT_NAME');

				if ($row['DISCOUNT_PRICE_PERCENT'] > 0 && !empty($row['DISCOUNT_PRICE_PERCENT_FORMATED']))
				{
					$rowData['COLUMN_LIST'][] = array(
						'CODE' => $value['id'],
						'NAME' => $value['name'],
						'VALUE' => $row['DISCOUNT_PRICE_PERCENT_FORMATED'],
						'IS_TEXT' => true,
						'HIDE_MOBILE' => !isset($mobileColumns[$value['id']])
					);
				}
			}
			elseif ($value['id'] === 'WEIGHT')
			{
				$value['name'] = Loc::getMessage('SBB_WEIGHT_NAME');

				if (!empty($row['WEIGHT_FORMATED']))
				{
					$rowData['COLUMN_LIST'][] = array(
						'CODE' => $value['id'],
						'NAME' => $value['name'],
						'VALUE' => $row['WEIGHT_FORMATED'],
						'IS_TEXT' => true,
						'HIDE_MOBILE' => !isset($mobileColumns[$value['id']])
					);
				}
			}
			elseif (!empty($row[$value['id'].'_SRC']))
			{
				$i = 0;

				foreach ($row[$value['id'].'_SRC'] as &$image)
				{
					$image['INDEX'] = $i++;
				}

				$rowData['COLUMN_LIST'][] = array(
					'CODE' => $value['id'],
					'NAME' => $value['name'],
					'VALUE' => $row[$value['id'].'_SRC'],
					'IS_IMAGE' => true,
					'HIDE_MOBILE' => !isset($mobileColumns[$value['id']])
				);
			}
			elseif (!empty($row[$value['id'].'_DISPLAY']))
			{
				$rowData['COLUMN_LIST'][] = array(
					'CODE' => $value['id'],
					'NAME' => $value['name'],
					'VALUE' => $row[$value['id'].'_DISPLAY'],
					'IS_TEXT' => true,
					'HIDE_MOBILE' => !isset($mobileColumns[$value['id']])
				);
			}
			elseif (!empty($row[$value['id'].'_LINK']))
			{
				$linkValues = array();

				foreach ($row[$value['id'].'_LINK'] as $index => $link)
				{
					$linkValues[] = array(
						'LINK' => $link,
						'IS_LAST' => !isset($row[$value['id'].'_LINK'][$index + 1])
					);
				}

				$rowData['COLUMN_LIST'][] = array(
					'CODE' => $value['id'],
					'NAME' => $value['name'],
					'VALUE' => $linkValues,
					'IS_LINK' => true,
					'HIDE_MOBILE' => !isset($mobileColumns[$value['id']])
				);
			}
			elseif (!empty($row[$value['id']]))
			{
				$rawValue = isset($row['~'.$value['id']]) ? $row['~'.$value['id']] : $row[$value['id']];
				$isHtml = !empty($row[$value['id'].'_HTML']);

				$rowData['COLUMN_LIST'][] = array(
					'CODE' => $value['id'],
					'NAME' => $value['name'],
					'VALUE' => $rawValue,
					'IS_TEXT' => !$isHtml,
					'IS_HTML' => $isHtml,
					'HIDE_MOBILE' => !isset($mobileColumns[$value['id']])
				);
			}
		}

		unset($value);
	}

	if (!empty($row['LABEL_ARRAY_VALUE']))
	{
		$labels = array();

		foreach ($row['LABEL_ARRAY_VALUE'] as $code => $value)
		{
			$labels[] = array(
				'NAME' => $value,
				'HIDE_MOBILE' => !isset($this->arParams['LABEL_PROP_MOBILE'][$code])
			);
		}

		$rowData['SHOW_LABEL'] = true;
		$rowData['LABEL_VALUES'] = $labels;
	}

    $totalQuantity += (int)$rowData["QUANTITY"];

	$result['BASKET_ITEM_RENDER_DATA'][] = $rowData;
}
// Пересчитываем итоговую сумму с учетом дополнительных скидок
$totalPriceAfterAdditionalDiscount = 0; // Общая сумма после применения доп скидок
$totalDiscountAmount = 0; // Общая сумма всех скидок (базовая + дополнительная)

foreach ($result['BASKET_ITEM_RENDER_DATA'] as $item) {
    if (!empty($item['SHOW_THREE_PRICES'])) {
        // Используем SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT для товаров с дополнительными скидками
        if (isset($item['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT'])) {
            $totalPriceAfterAdditionalDiscount += $item['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT'];
        } else {
            // Если нет в rowData, используем текущую цену
            $totalPriceAfterAdditionalDiscount += $item['SUM_PRICE'];
        }
        
        // Рассчитываем общую скидку для этого товара
        $originalSum = 0;
        if (isset($item['SUM_ORIGINAL_PRICE'])) {
            $originalSum = $item['SUM_ORIGINAL_PRICE'];
        } elseif (!empty($item['oldPrice'])) {
            $originalSum = $item['oldPrice'];
        } else {
            $originalSum = $item['SUM_FULL_PRICE'] ?? $item['SUM_PRICE'];
        }
        
        $itemFinalPrice = isset($item['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT']) 
            ? $item['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT'] 
            : $item['SUM_PRICE'];
        $itemDiscount = $originalSum - $itemFinalPrice;
        $totalDiscountAmount += $itemDiscount;
    } else {
        // Для товаров без доп скидки используем стандартную логику
        $totalPriceAfterAdditionalDiscount += $item['SUM_PRICE'];
        
        // Рассчитываем скидку для этого товара
        $originalSum = 0;
        if (empty($item["oldPrice"])) {
            $originalSum = $item["SUM_PRICE"];
        } else {
            $originalSum = $item["oldPrice"];
        }
        $itemDiscount = $originalSum - $item['SUM_PRICE'];
        $totalDiscountAmount += $itemDiscount;
    }
}

// Обновляем итоговую цену корзины с учетом дополнительных скидок
$result['allSum'] = $totalPriceAfterAdditionalDiscount;
$result['allSum_FORMATED'] = CCurrencyLang::CurrencyFormat($totalPriceAfterAdditionalDiscount, $result['CURRENCY'], true);

// Обновляем PRICE_WITHOUT_DISCOUNT с учетом ORIGINAL_PRICE
$totalPriceWithoutDiscount = 0;
foreach ($result['BASKET_ITEM_RENDER_DATA'] as $item) {
    if (!empty($item['SHOW_THREE_PRICES'])) {
        if (isset($item['SUM_ORIGINAL_PRICE'])) {
            $totalPriceWithoutDiscount += $item['SUM_ORIGINAL_PRICE'];
        } elseif (!empty($item['oldPrice'])) {
            $totalPriceWithoutDiscount += $item['oldPrice'];
        } else {
            $totalPriceWithoutDiscount += $item['SUM_FULL_PRICE'] ?? $item['SUM_PRICE'];
        }
    } else {
        if (empty($item["oldPrice"])) {
            $totalPriceWithoutDiscount += $item["SUM_PRICE"];
        } else {
            $totalPriceWithoutDiscount += $item["oldPrice"];
        }
    }
}

if ($totalPriceWithoutDiscount > 0) {
    $result['PRICE_WITHOUT_DISCOUNT'] = CCurrencyLang::CurrencyFormat($totalPriceWithoutDiscount, $result['CURRENCY'], true);
}

$sumPriceDollar = Base::isEnLang() ? Base::formatDollar($sumPriceDollar) : false;
$result["QUANTITY"] = $totalQuantity;
$totalData = array(
	'DISABLE_CHECKOUT' => (int)$result['ORDERABLE_BASKET_ITEMS_COUNT'] === 0,
	'PRICE' => $result['allSum'],
	'PRICE_FORMATED' => $result['allSum_FORMATED'],
	'PRICE_WITHOUT_DISCOUNT_FORMATED' => $result['PRICE_WITHOUT_DISCOUNT'] ?? '',
	'CURRENCY' => $result['CURRENCY'],
    'SUM_PRICE_DOLLAR' => $sumPriceDollar,
    'OLD_SUM_PRICE' => CCurrencyLang::CurrencyFormat($totalPriceWithoutDiscount, "RUB"),
    'OLD_SUM_PRICE_DOLLAR' => Base::formatDollar($oldSumPriceDollar),
    'SHOW_OLD_SUM_PRICE' => !empty($totalPriceWithoutDiscount) && $totalPriceWithoutDiscount !== $result["allSum"],
);

if ($result['DISCOUNT_PRICE_ALL'] > 0)
{
	$totalData['DISCOUNT_PRICE_FORMATED'] = $result['DISCOUNT_PRICE_FORMATED'];
}

if ($result['allWeight'] > 0)
{
	$totalData['WEIGHT_FORMATED'] = $result['allWeight_FORMATED'];
}

if ($this->priceVatShowValue === 'Y')
{
	$totalData['SHOW_VAT'] = true;
	$totalData['VAT_SUM_FORMATED'] = $result['allVATSum_FORMATED'];
	$totalData['SUM_WITHOUT_VAT_FORMATED'] = $result['allSum_wVAT_FORMATED'];
}

$totalData['QUANTITY'] = $result['QUANTITY'];


if ($this->hideCoupon !== 'Y' && !empty($result['COUPON_LIST']))
{
	$totalData['COUPON_LIST'] = $result['COUPON_LIST'];

	foreach ($totalData['COUPON_LIST'] as &$coupon)
	{
		if ($coupon['JS_STATUS'] === 'ENTERED')
		{
			$coupon['CLASS'] = 'danger';
		}
		elseif ($coupon['JS_STATUS'] === 'APPLYED')
		{
			$coupon['CLASS'] = 'muted';
		}
		else
		{
			$coupon['CLASS'] = 'danger';
		}
	}
}

$result['TOTAL_RENDER_DATA'] = $totalData;
