<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
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
    $properties = [];
    $rsProperties = \CIBlockElement::GetList(
        [],
        [
            "IBLOCK_ID" => \Helper::OFFERS_IBLOCK_ID,
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

    while ($property = $rsProperties->GetNext()) {
        $properties[$property["ID"]] = $property;
    }

    foreach ($arResult["CATEGORIES"] as &$category) {
        foreach ($category as &$item) {
            $item["SIZE"] = $properties[$item["PRODUCT_ID"]]["PROPERTY_SIZE_REF_VALUE"];
            $item["NAME"] = \Helper::isEnLang() && !empty($properties[$item["PRODUCT_ID"]]["PROPERTY_NAME_EN_VALUE"])
                ? $properties[$item["PRODUCT_ID"]]["PROPERTY_NAME_EN_VALUE"] : $item["NAME"];
        }
        unset($item);
    }
    unset($category);
}

$arResult["NUM_PRODUCTS"] = $totalQuantity;
