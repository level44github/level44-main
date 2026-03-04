<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
use Bitrix\Highloadblock as HL;
use Level44\Product;

\Bitrix\Main\Loader::includeModule("highloadblock");

/** Скрытие подарочных товаров (по ID/офферу и цене 0) в мини-корзине */
if (!empty($arResult["CATEGORIES"]) && class_exists(\Level44\Event\GiftOver40kHandlers::class)) {
    foreach ($arResult["CATEGORIES"] as $catKey => &$category) {
        if (!is_array($category)) {
            continue;
        }
        foreach ($category as $itemKey => $item) {
            $productId = (int)($item["PRODUCT_ID"] ?? 0);
            $price = (float)($item["PRICE"] ?? 0);
            if (\Level44\Event\GiftOver40kHandlers::isGiftItem($productId, $price)) {
                unset($arResult["CATEGORIES"][$catKey][$itemKey]);
            }
        }
        if (is_array($arResult["CATEGORIES"][$catKey])) {
            $arResult["CATEGORIES"][$catKey] = array_values($arResult["CATEGORIES"][$catKey]);
        }
    }
    unset($category);
}

$totalQuantity = 0;
$productIds = [];
foreach ($arResult["CATEGORIES"] as $category) {
    foreach ($category as $item) {
        $totalQuantity += (int)$item["QUANTITY"];
        $productIds[] = $item["PRODUCT_ID"];
    }
}


if (!empty($productIds)) {

    $sizeRefTableName = \Level44\Base::SIZE_HL_TBL_NAME;

    $sizes = [];

    $hlblock = HL\HighloadBlockTable::getList([
        'filter' => [
            '=TABLE_NAME' => $sizeRefTableName
        ]
    ])->fetch();

    if ($hlblock) {
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entityClass = $entity->getDataClass();

        $res = $entityClass::getList(
            [
                "select" => [
                    "ID",
                    "UF_NAME",
                    "UF_XML_ID"
                ],
            ]
        );

        while ($size = $res->fetch()) {
            $sizes[$size["UF_XML_ID"]] = $size["UF_NAME"];
        }
    }


    $properties = [];
    $rsProperties = \CIBlockElement::GetList(
        [],
        [
            "IBLOCK_ID" => \Level44\Base::OFFERS_IBLOCK_ID,
            "ID" => $productIds,
        ],
        false,
        [],
        [
            "ID",
            "IBLOCK_ID",
            "PROPERTY_SIZE_REF",
            "PROPERTY_NAME_EN"
        ]
    );

    $products = \CCatalogSku::getProductList($productIds, \Level44\Base::OFFERS_IBLOCK_ID);
    $products = array_map(function ($item) {
        return $item["ID"];
    }, $products);

    $rsProductsData = \CIBlockElement::GetList(
        [],
        [
            "ID" => array_values($products),
        ],
        false,
        false,
        [
            "ID",
            "IBLOCK_ID",
            "PROPERTY_PRICE_DOLLAR"
        ]
    );
    $productsData = [];
    while ($productData = $rsProductsData->GetNext()) {
        $productsData[$productData["ID"]] = $productData;
    }
    $obProduct = new Product();
    $ecommerceData = $obProduct->getEcommerceData(array_values($products));

    foreach ($products as $key => &$product) {
        $product = $productsData[$product];
    }
    unset($product);

    $products = array_filter($products);


    while ($property = $rsProperties->GetNext()) {
        $properties[$property["ID"]] = $property;
    }

    foreach ($arResult["CATEGORIES"] as &$category) {
        foreach ($category as &$item) {
            $item["SIZE"] = $properties[$item["PRODUCT_ID"]]["PROPERTY_SIZE_REF_VALUE"];
            if (!empty($sizes[$item["SIZE"]])) {
                $item["SIZE"] = $sizes[$item["SIZE"]];
            }

            $item["NAME"] = \Level44\Base::getMultiLang(
                $item["NAME"],
                $properties[$item["PRODUCT_ID"]]["PROPERTY_NAME_EN_VALUE"]
            );

            $item["PRICE_DOLLAR"] = \Level44\Base::getDollarPrice(
                $item["PRICE"],
                $products[$item["PRODUCT_ID"]]["PROPERTY_PRICE_DOLLAR_VALUE"]
            );
            $item = array_merge($item, (array)$ecommerceData[$products[$item["PRODUCT_ID"]]["ID"]]["prices"]);
        }
        unset($item);
    }
    unset($category);
}

$arResult["NUM_PRODUCTS"] = $totalQuantity;
