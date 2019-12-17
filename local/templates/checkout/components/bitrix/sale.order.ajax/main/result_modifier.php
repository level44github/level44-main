<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$columns = [];
$fulls = [];

foreach ($arResult["ORDER_PROP"]["USER_PROPS_Y"] as &$prop) {
    if ($prop["CODE"] === "ADDRESS") {
        $arResult["ORDER_PROP_ADDRESS"] = $prop;
        continue;
    }

    if (in_array($prop["CODE"], ["EMAIL", "PHONE"])) {
        $columns[] = $prop;
    } else {
        $fulls[] = $prop;
    }
}

unset($prop);

$basketItemsQuantity = 0;

foreach ($arResult["BASKET_ITEMS"] as &$basketItem) {
    $basketItemsQuantity += $basketItem["QUANTITY"];
    if (!empty($basketItem["PREVIEW_PICTURE_SRC"])) {
        $basketItem["PICTURE"] = $basketItem["PREVIEW_PICTURE_SRC"];
    } elseif ($basketItem["DETAIL_PICTURE_SRC"]) {
        $basketItem["PICTURE"] = $basketItem["DETAIL_PICTURE_SRC"];
    } else {
        $basketItem["PICTURE"] = "";
    }
}
unset($basketItem);

$arResult["BASKET_ITEMS_QUANTITY"] = $basketItemsQuantity;

if (count($columns) & 1) {
    array_unshift($fulls, array_pop($columns));
}

$columns = array_chunk($columns, 2);

$resultProps = $columns || $fulls ? [
    "COLUMNS" => $columns,
    "FULLS" => $fulls,
] : [];

$arResult["ORDER_PROP"]["USER_PROPS_Y"] = $resultProps;

foreach ($arResult["DELIVERY"] as $key => &$delivery) {
    $delivery["CHECKED"] = $delivery["CHECKED"] === "Y";
    if ((int)$delivery["ID"] === \Helper::DELIVERY_COURIER) {
        $delivery["PERIOD_TEXT"] = "1 день";
    }

    $delivery["PRICE_PERIOD_TEXT"] = $delivery["PERIOD_TEXT"];
    $delivery["PRICE_PERIOD_TEXT"] = $delivery["PRICE_PERIOD_TEXT"] .
        (!empty($delivery["PRICE_PERIOD_TEXT"]) ? ", " : "");
    if (empty($delivery["PRICE_FORMATED"]) || (int)$delivery["PRICE"] <= 0) {
        $delivery["PRICE_FORMATED"] = "бесплатно";
    }
    $delivery["PRICE_PERIOD_TEXT"] .= $delivery["PRICE_FORMATED"];
}
unset($delivery);

