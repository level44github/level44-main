<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$totalQuantity = 0;
foreach ($arResult["CATEGORIES"] as $category) {
    foreach ($category as $item) {
        $totalQuantity += (int)$item["QUANTITY"];
    }
}

$arResult["NUM_PRODUCTS"] = $totalQuantity;
