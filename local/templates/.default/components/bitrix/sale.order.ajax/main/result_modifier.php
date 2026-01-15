<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;
use Bitrix\Sale\Location\LocationTable;
use Level44\Base;
use Level44\Product;

$request = Context::getCurrent()->getRequest();

$columns = [];
$fulls = [];

foreach ($arResult["JS_DATA"]["ORDER_PROP"]["properties"] as &$prop) {
    $prop["REQUIRED"] = $prop["REQUIRED"] === "Y";

    $prop["VALUE"] = reset($prop["VALUE"]);
    if ($prop["CODE"] === "LOCATION") {
        $prop["VALUE"] = CSaleLocation::getLocationIDbyCODE($prop["VALUE"]);
    }

    $prop["FIELD_NAME"] = "ORDER_PROP_" . $prop["ID"];

    if (empty($prop["DESCRIPTION"])) {
        $prop["DESCRIPTION"] = Loc::getMessage("INPUT") . strtolower($prop["NAME"]);
    }

    $prop["VALIDATION_CLASS"] = "";

    if ($prop["CODE"] === "EMAIL") {
        $prop["VALIDATION_CLASS"] = "js-form__email";
        $prop["ERROR_MES_TYPE"] = "EMAIL_ERROR_MES";
    }

    if ($prop["CODE"] === "PHONE") {
        $prop["VALIDATION_CLASS"] = "js-form__phone";
    }

    if (empty($prop["ERROR_MES_TYPE"])) {
        $prop["ERROR_MES_TYPE"] = "REQUIRED_ERROR_MES";
    }

    if ($prop["CODE"] === "ADDRESS") {
        $arResult["ORDER_PROP_ADDRESS"] = $prop;
        continue;
    }

    if ($prop["CODE"] === "ADDRESS_SDEK") {
        $arResult["ORDER_PROP_ADDRESS_SDEK"] = $prop;
        continue;
    }

    if ($prop["CODE"] === "DELIVERY_DATE") {
        $arResult["ORDER_PROP_DELIVERY_DATE"] = $prop;
        continue;
    }

    if ($prop["CODE"] === "TIME_INTERVAL") {
        $arResult["ORDER_PROP_TIME_INTERVAL"] = $prop;
        continue;
    }

    if ($prop["CODE"] === "LOCATION") {
        if (empty($prop["VALUE"])) {
            $arResult["DELIVERY"] = [];
            $arResult["PAY_SYSTEM"] = [];
        }

        $locPath = [];
        if ((int)$prop["VALUE"] > 0) {
            $locPath = LocationTable::getPathToNode($prop["VALUE"], [])->fetchAll();
            $locPath = current($locPath);
        }

        $countryId = !empty($locPath) ? (int)$locPath["ID"] : false;
        if (!$countryId) {
            $countryId = 1;
        }

        $arResult["OUT_RUSSIA"] = !in_array($countryId, Base::getSngCountriesId(), true);

        if ($request->getPost("out_russia") === "Y") {
            $arResult["OUT_RUSSIA"] = true;
        } elseif ($request->getPost("out_russia") === "N") {
            $arResult["OUT_RUSSIA"] = false;
        }
        $prop["OUT_RUSSIA"] = $arResult["OUT_RUSSIA"];
        if ($prop["OUT_RUSSIA"]) {
            $prop["NAME"] = Base::getMultiLang("Страна", "Country");
        }
    }

    if (in_array($prop["CODE"], ["EMAIL", "PHONE", "FIRST_NAME", "LAST_NAME", "SECOND_NAME"])) {
        $columns[] = $prop;
    } else {
        $fulls[] = $prop;
    }
}

unset($prop);

$basketItemsQuantity = 0;

foreach ($arResult["BASKET_ITEMS"] as $item) {
    $basketProductIds[] = (int)$item["PRODUCT_ID"];
}

$arProductsAdd = [];

if (!empty($basketProductIds)) {
    $resProduct = CIBlockElement::GetList(
        [],
        [
            "=ID" => $basketProductIds
        ],
        false,
        false,
        [
            "ID",
            "PROPERTY_NAME_EN",
            "PROPERTY_COLOR_REF",
        ]
    );


    while ($product = $resProduct->GetNext()) {
        $arProductsAdd[$product["ID"]] = [
            "NAME_EN"      => $product["PROPERTY_NAME_EN_VALUE"],
            "COLOR_XML_ID" => $product["PROPERTY_COLOR_REF_VALUE"],
        ];
    }

}

$productList = CCatalogSKU::getProductList($basketProductIds);

$productIds = [];
foreach ($productList as $offerId => $product) {
    $productIds[$offerId] = $product["ID"];
}

$productsData = $productIds;

$rsProductsData = CIBlockElement::GetList(
    [],
    [
        "ID"        => array_values($productsData),
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

$sumPriceDollar = 0;
$oldSumPrice = 0;
$oldSumPriceDollar = 0;
$productsDataExt = [];
while ($productData = $rsProductsData->GetNext()) {
    $productsDataExt[$productData["ID"]] = $productData;
}

$product = new Product();
$ecommerceData = $product->getEcommerceData(array_values($productsData));

foreach ($productsData as $key => &$productsDataItem) {
    $productId = $productsDataItem;
    $productsDataExt[$productId]["prices"] = $ecommerceData[$productId]["prices"];
    $productsDataItem = $productsDataExt[$productId];
}
unset($productsDataItem);

$productsData = array_filter($productsData);

$products = array_map(function ($productId) {
    return [
        "ID" => $productId
    ];
}, $productIds);

Base::setColorOffers($products);

foreach ($arResult["BASKET_ITEMS"] as &$basketItem) {
    $basketItem["COLOR"] = $products[$basketItem["PRODUCT_ID"]];
    $basketItemsQuantity += $basketItem["QUANTITY"];
    if (!empty($basketItem["PREVIEW_PICTURE_SRC"])) {
        $basketItem["PICTURE"] = $basketItem["PREVIEW_PICTURE_SRC"];
    } elseif ($basketItem["DETAIL_PICTURE_SRC"]) {
        $basketItem["PICTURE"] = $basketItem["DETAIL_PICTURE_SRC"];
    } else {
        $basketItem["PICTURE"] = "";
    }

    $basketItem["PRICE_DOLLAR"] = (int)$productsData[$basketItem["PRODUCT_ID"]]["PROPERTY_PRICE_DOLLAR_VALUE"];
    $itemPriceDollar = 0;

    if ($basketItem["PRICE_DOLLAR"] <= 0) {
        $itemPriceDollar = Base::getDollarPrice(
            $basketItem["PRICE"],
            null,
            true
        );
    } else {
        $itemPriceDollar = $basketItem["PRICE_DOLLAR"];
    }

    $basketItem = array_merge($basketItem, $productsData[$basketItem["PRODUCT_ID"]]["prices"]);
    $basketItem["oldPrice"] = $basketItem["oldPrice"] * $basketItem["QUANTITY"];
    $basketItem["oldPriceDollar"] = $basketItem["oldPriceDollar"] * $basketItem["QUANTITY"];
    $basketItem["oldPriceFormat"] = CCurrencyLang::CurrencyFormat($basketItem["oldPrice"], "RUB");
    $basketItem["oldPriceDollarFormat"] = Base::formatDollar($basketItem["oldPriceDollar"]);

    $itemPriceDollar = $itemPriceDollar * $basketItem["QUANTITY"];

    $sumPriceDollar += $itemPriceDollar;

    if (empty($basketItem["oldPrice"])) {
        $oldSumPrice += $basketItem["SUM_NUM"];
        $oldSumPriceDollar += $itemPriceDollar;
    } else {
        $oldSumPrice += $basketItem["oldPrice"];
        $oldSumPriceDollar += $basketItem["oldPriceDollar"];
    }

    $basketItem["PRICE_DOLLAR"] = Base::isEnLang() ? Base::formatDollar($itemPriceDollar) : false;

    $basketItem["NAME"] = Base::getMultiLang(
        $basketItem["NAME"],
        $arProductsAdd[$basketItem["PRODUCT_ID"]]["NAME_EN"]
    );

    if (!empty($basketItem["COLOR"])) {
        $basketItem["PROPS"]["COLOR_REF"] = [
            "CODE"  => "COLOR_REF",
            "VALUE" => $basketItem["COLOR"]["COLOR_NAME"],
        ];
    }

    unset($prop);
}
unset($basketItem);

// Применяем дополнительные скидки для товаров категории sale
if (!empty($arResult["BASKET_ITEMS"]) && \Bitrix\Main\Loader::includeModule('iblock')) {
    $arResult["BASKET_ITEMS"] = \Level44\Event\SaleCategoryDiscountHandler::applyDiscounts($arResult["BASKET_ITEMS"]);
    
    // Пересчитываем суммы после применения скидок
    $oldSumPrice = 0;
    $oldSumPriceDollar = 0;
    $totalPriceAfterAdditionalDiscount = 0; // Общая сумма после применения доп скидок
    $totalDiscountAmount = 0; // Общая сумма всех скидок (базовая + дополнительная)
    
    foreach ($arResult["BASKET_ITEMS"] as &$basketItem) {
        // Если есть дополнительные скидки, НЕ перезаписываем SUM_NUM и SUM
        // Они должны оставаться как цена после первой скидки для отображения в чекауте
        // Дополнительная скидка будет применена только при сохранении заказа
        if (!empty($basketItem['SHOW_THREE_PRICES'])) {
            // Используем SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT только для расчета итоговой суммы
            // но НЕ перезаписываем SUM_NUM и SUM товара
            if (isset($basketItem['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT'])) {
                // Добавляем к общей сумме после доп скидок (для расчета финальной цены заказа)
                $totalPriceAfterAdditionalDiscount += $basketItem['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT'];
            } else {
                $totalPriceAfterAdditionalDiscount += $basketItem['SUM_NUM'];
            }
            
            // Для расчета старой цены используем ORIGINAL_PRICE
            $originalSum = 0;
            if (isset($basketItem['SUM_ORIGINAL_PRICE'])) {
                $originalSum = $basketItem['SUM_ORIGINAL_PRICE'];
                $oldSumPrice += $originalSum;
            } else {
                $originalSum = $basketItem['SUM_BASE'] ?? $basketItem['SUM_NUM'];
                $oldSumPrice += $originalSum;
            }
            
            // Рассчитываем общую скидку для этого товара (ORIGINAL_PRICE - PRICE_AFTER_ADDITIONAL_DISCOUNT)
            $itemDiscount = $originalSum - ($basketItem['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT'] ?? $basketItem['SUM_NUM']);
            $totalDiscountAmount += $itemDiscount;
        } else {
            // Для товаров без доп скидки используем стандартную логику
            $totalPriceAfterAdditionalDiscount += $basketItem['SUM_NUM'];
            $originalSum = 0;
            if (empty($basketItem["oldPrice"])) {
                $originalSum = $basketItem["SUM_NUM"];
                $oldSumPrice += $originalSum;
            } else {
                $originalSum = $basketItem["oldPrice"];
                $oldSumPrice += $originalSum;
            }
            
            // Рассчитываем скидку для этого товара
            $itemDiscount = $originalSum - $basketItem['SUM_NUM'];
            $totalDiscountAmount += $itemDiscount;
        }
    }
    unset($basketItem);
    
    // Обновляем итоговые суммы
    $arResult["OLD_SUM_PRICE"] = CCurrencyLang::CurrencyFormat($oldSumPrice, "RUB");
    $arResult["OLD_SUM_PRICE_VALUE"] = $oldSumPrice;
    $arResult["OLD_SUM_PRICE_DOLLAR"] = Base::formatDollar($oldSumPriceDollar);
    $arResult["SHOW_OLD_SUM_PRICE"] = !empty($oldSumPrice) && $oldSumPrice !== $totalPriceAfterAdditionalDiscount;
    
    // Сохраняем общую сумму скидок для отображения
    $arResult["TOTAL_DISCOUNT_AMOUNT"] = $totalDiscountAmount;
    $arResult["TOTAL_DISCOUNT_AMOUNT_FORMATED"] = CCurrencyLang::CurrencyFormat($totalDiscountAmount, "RUB");
    
    // Обновляем финальную цену заказа с учетом дополнительных скидок
    if (isset($arResult["JS_DATA"]["TOTAL"]["ORDER_PRICE"])) {
        // Пересчитываем ORDER_PRICE на основе сумм после доп скидок
        $arResult["JS_DATA"]["TOTAL"]["ORDER_PRICE"] = $totalPriceAfterAdditionalDiscount;
        $arResult["JS_DATA"]["TOTAL"]["ORDER_PRICE_FORMATED"] = SaleFormatCurrency($totalPriceAfterAdditionalDiscount, 'RUB');
        
        // Обновляем PRICE_WITHOUT_DISCOUNT_VALUE для корректного отображения скидки
        $arResult["JS_DATA"]["TOTAL"]["PRICE_WITHOUT_DISCOUNT_VALUE"] = $oldSumPrice;
        $arResult["JS_DATA"]["TOTAL"]["PRICE_WITHOUT_DISCOUNT"] = CCurrencyLang::CurrencyFormat($oldSumPrice, 'RUB');
        
        // Обновляем общую сумму скидок в JS_DATA
        $arResult["JS_DATA"]["TOTAL"]["DISCOUNT_PRICE_ALL"] = $totalDiscountAmount;
        $arResult["JS_DATA"]["TOTAL"]["DISCOUNT_PRICE_ALL_FORMATED"] = CCurrencyLang::CurrencyFormat($totalDiscountAmount, 'RUB');
        
        // Обновляем ORDER_TOTAL_PRICE (цена заказа + доставка)
        $deliveryPrice = $arResult["JS_DATA"]["TOTAL"]["DELIVERY_PRICE"] ?? 0;
        $arResult["JS_DATA"]["TOTAL"]["ORDER_TOTAL_PRICE"] = \Bitrix\Sale\PriceMaths::roundPrecision(
            $totalPriceAfterAdditionalDiscount + $deliveryPrice
        );
        $arResult["JS_DATA"]["TOTAL"]["ORDER_TOTAL_PRICE_FORMATED"] = SaleFormatCurrency(
            $arResult["JS_DATA"]["TOTAL"]["ORDER_TOTAL_PRICE"],
            'RUB'
        );
        
        // Обновляем основные поля заказа
        $arResult["ORDER_TOTAL_PRICE"] = $arResult["JS_DATA"]["TOTAL"]["ORDER_TOTAL_PRICE"];
        $arResult["ORDER_TOTAL_PRICE_NEW"] = $arResult["JS_DATA"]["TOTAL"]["ORDER_TOTAL_PRICE_FORMATED"];
        $arResult["ORDER_PRICE"] = $totalPriceAfterAdditionalDiscount;
    }
}

$arResult["BASKET_ITEMS_QUANTITY"] = $basketItemsQuantity;
$arResult["SUM_PRICE_DOLLAR"] = Base::isEnLang() ? Base::formatDollar($sumPriceDollar) : false;
$arResult["OLD_SUM_PRICE"] = CCurrencyLang::CurrencyFormat($oldSumPrice, "RUB");

$arResult["OLD_SUM_PRICE_VALUE"] = $oldSumPrice;

$arResult["OLD_SUM_PRICE_DOLLAR"] = Base::formatDollar($oldSumPriceDollar);
$arResult["SHOW_OLD_SUM_PRICE"] = !empty($oldSumPrice) && $oldSumPrice !== $arResult["ORDER_PRICE"];

//if (count($columns) & 1) {
//    array_unshift($fulls, array_pop($columns));
//}

$columns = array_chunk($columns, 2);

$resultProps = $columns || $fulls ? [
    "COLUMNS" => $columns,
    "FULLS"   => $fulls,
] : [];

$arResult["ORDER_PROP"]["USER_PROPS_Y"] = $resultProps;

$currentDelivery = current(array_filter($arResult["DELIVERY"], fn($delivery) => $delivery["CHECKED"]));

if (!empty($currentDelivery)) {
    $arResult["CURRENT_DELIVERY"] = $currentDelivery;
}


if ($arResult["USER_VALS"]["CONFIRM_ORDER"] == "Y") {
    $arResult["IS_CASH"] = !empty($arResult["ORDER"]) && !empty($arResult["PAY_SYSTEM"])
        && $arResult["PAY_SYSTEM"]["IS_CASH"] === "Y"
        && strripos($arResult["PAY_SYSTEM"]["ACTION_FILE"], "cash") !== false
        && !empty($arResult["PAY_SYSTEM"]["ACTION_FILE"]);
} else {
    $arResult["PAY_SYSTEM"] = array_filter($arResult["PAY_SYSTEM"], fn($item) => $item['CODE'] !== 'cloudpayments_crm');
}

$dollarTotalPrice = Base::getDollarPrice($arResult['CURRENT_DELIVERY']['PRICE'], null, true) + $sumPriceDollar;
$arResult["ORDER_TOTAL_PRICE_DOLLAR"] = $dollarTotalPrice <= 0 || !Base::isEnLang() ? false
    : Base::formatDollar($dollarTotalPrice);

$curDelPriceWithDisc=$arResult['CURRENT_DELIVERY']['PRICE'];


if (isset($arResult['CURRENT_DELIVERY']['DELIVERY_DISCOUNT_PRICE']))
{
    $curDelPriceWithDisc=$arResult['CURRENT_DELIVERY']['DELIVERY_DISCOUNT_PRICE'];
}


if (!empty($arResult['CURRENT_DELIVERY'])) {
    // Если ORDER_PRICE был обновлен после применения доп скидок, используем его
    $basketPrice = isset($arResult["ORDER_PRICE"]) ? $arResult["ORDER_PRICE"] : 
        ($arResult["JS_DATA"]["TOTAL"]["ORDER_PRICE"] - $arResult["JS_DATA"]["TOTAL"]["DELIVERY_PRICE"]);
    
    $arResult["JS_DATA"]["TOTAL"]["ORDER_TOTAL_PRICE"] = \Bitrix\Sale\PriceMaths::roundPrecision(
        $basketPrice + $curDelPriceWithDisc
    );

    $arResult["JS_DATA"]["TOTAL"]["ORDER_TOTAL_PRICE_FORMATED"] = SaleFormatCurrency($arResult["JS_DATA"]["TOTAL"]["ORDER_TOTAL_PRICE"], 'RUB');
}

$arResult["ORDER_TOTAL_PRICE"] = $arResult["JS_DATA"]["TOTAL"]["ORDER_TOTAL_PRICE"];

$arResult["ORDER_TOTAL_PRICE_NEW"] = $arResult["JS_DATA"]["TOTAL"]["ORDER_TOTAL_PRICE_FORMATED"];

$user = UserTable::getList([
    'filter' => ['=ID' => $arResult['ORDER_DATA']['USER_ID']],
    'select' => ['ID', 'UF_SUBSCRIBED_TO_NEWSLETTER']
])->fetch();

$arResult['SUBSCRIBE_CHECKED'] = $user['UF_SUBSCRIBED_TO_NEWSLETTER'] === '1' || $_POST["subscribe"] === 'Y';


$bonusPaymentPropertyCode = 'BONUS_PAYMENT_ALLOWED'; 

$arResult['BONUS_PAYMENT_ALLOWED'] = true; // По умолчанию разрешено

if (!empty($arResult["BASKET_ITEMS"]) && !empty($bonusPaymentPropertyCode)) {
    $basketProductIds = [];
    foreach ($arResult["BASKET_ITEMS"] as $item) {
        $basketProductIds[] = (int)$item["PRODUCT_ID"];
    }
    
    if (!empty($basketProductIds)) {
        // Получаем информацию о торговых предложениях (SKU) - определяем родительские товары
        $productList = CCatalogSKU::getProductList($basketProductIds);
        $parentProductIds = [];
        foreach ($productList as $offerId => $product) {
            $parentProductIds[$offerId] = (int)$product["ID"];
        }
        
        // Формируем список ID товаров для проверки: для SKU берем родительский товар, для обычных - сам товар
        $productsToCheck = [];
        foreach ($basketProductIds as $productId) {
            if (isset($parentProductIds[$productId])) {
                // Это SKU - проверяем родительский товар
                $productsToCheck[] = $parentProductIds[$productId];
            } else {
                // Это обычный товар - проверяем его
                $productsToCheck[] = $productId;
            }
        }
        
        // Убираем дубликаты (если несколько SKU одного товара)
        $productsToCheck = array_unique($productsToCheck);
        
        // Получаем все родительские товары с их свойствами
        $resProducts = CIBlockElement::GetList(
            [],
            [
                "=ID" => $productsToCheck
            ],
            false,
            false,
            ["ID", "PROPERTY_{$bonusPaymentPropertyCode}"]
        );
        
        $productsWithProperty = [];
        while ($product = $resProducts->GetNext()) {
            $propertyValue = $product["PROPERTY_{$bonusPaymentPropertyCode}_VALUE"] ?? null;
            // Проверяем, что свойство заполнено и не пустое
            if (!empty($propertyValue) && $propertyValue !== false) {
                $productsWithProperty[] = (int)$product["ID"];
            }
        }
        
        // Проверяем, что все родительские товары имеют свойство
        $allProductsHaveProperty = true;
        foreach ($productsToCheck as $productId) {
            if (!in_array($productId, $productsWithProperty)) {
                $allProductsHaveProperty = false;
                break;
            }
        }
        
        $arResult['BONUS_PAYMENT_ALLOWED'] = $allProductsHaveProperty;
    }
}