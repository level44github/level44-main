<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

\Bitrix\Main\Loader::includeModule("highloadblock");

use Bitrix\Main\Localization\Loc;
use Bitrix\Highloadblock as HL;

$columns = [];
$fulls = [];

foreach ($arResult["ORDER_PROP"]["USER_PROPS_Y"] as &$prop) {
    if (empty($prop["DESCRIPTION"])) {
        $prop["DESCRIPTION"] = Loc::getMessage("INPUT") . strtolower($prop["NAME"]);
    }

    $prop["VALIDATION_CLASS"] = "";

    if ($prop["CODE"] === "EMAIL") {
        $prop["VALIDATION_CLASS"] = "js-form__email";
    }

    if ($prop["CODE"] === "PHONE") {
        $prop["VALIDATION_CLASS"] = "js-form__phone";
    }

    if ($prop["CODE"] === "ADDRESS") {
        $arResult["ORDER_PROP_ADDRESS"] = $prop;
        continue;
    }

    if ($prop["CODE"] === "LOCATION" && empty($prop["VALUE"])) {
        $arResult["DELIVERY"] = [];
        $arResult["PAY_SYSTEM"] = [];
    }

    if (in_array($prop["CODE"], ["EMAIL", "PHONE"])) {
        $columns[] = $prop;
    } else {
        $fulls[] = $prop;
    }
}

unset($prop);

$basketItemsQuantity = 0;

$colorsRef = [];

$hlblock = HL\HighloadBlockTable::getList([
    'filter' => [
        '=TABLE_NAME' => "eshop_color_reference"
    ]
])->fetch();

if ($hlblock) {
    $entity = HL\HighloadBlockTable::compileEntity($hlblock);
    $entityClass = $entity->getDataClass();

    $res = $entityClass::getList(
        [
            "select" => [
                "ID",
                "UF_XML_ID",
                "UF_NAME_EN",
            ]
        ]
    );

    while ($color = $res->fetch()) {
        $colorsRef[$color["UF_XML_ID"]] = $color;
    }
}


foreach ($arResult["BASKET_ITEMS"] as $item) {
    $basketProductIds[] = (int)$item["PRODUCT_ID"];
}

$arProductsAdd = [];

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
            "PROPERTY_NAME_EN",
            "PROPERTY_COLOR_REF",
        ]
    );


    while ($product = $resProduct->GetNext()) {
        $arProductsAdd[$product["ID"]] = [
            "NAME_EN" => $product["PROPERTY_NAME_EN_VALUE"],
            "COLOR_XML_ID" => $product["PROPERTY_COLOR_REF_VALUE"],
        ];
    }

}

foreach ($arResult["BASKET_ITEMS"] as &$basketItem) {
    $basketItemsQuantity += $basketItem["QUANTITY"];
    if (!empty($basketItem["PREVIEW_PICTURE_SRC"])) {
        $basketItem["PICTURE"] = $basketItem["PREVIEW_PICTURE_SRC"];
    } elseif ($basketItem["DETAIL_PICTURE_SRC"]) {
        $basketItem["PICTURE"] = $basketItem["DETAIL_PICTURE_SRC"];
    } else {
        $basketItem["PICTURE"] = "";
    }

    $basketItem["NAME"] = \Level44\Base::getMultiLang(
        $basketItem["NAME"],
        $arProductsAdd[$basketItem["PRODUCT_ID"]]["NAME_EN"]
    );

    foreach ($basketItem["PROPS"] as &$prop) {
        if ($prop["CODE"] === "COLOR_REF") {
            $prop["VALUE"] = \Level44\Base::getMultiLang(
                $prop["VALUE"],
                $colorsRef[$arProductsAdd[$basketItem["PRODUCT_ID"]]["COLOR_XML_ID"]]["UF_NAME_EN"]
            );
        }
    }
    unset($prop);
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
    if (in_array((int)$delivery["ID"], \Level44\Base::DELIVERY_COURIER)) {
        $delivery["PERIOD_TEXT"] = Loc::getMessage("DAY");
    }

    $delivery["PRICE_PERIOD_TEXT"] = $delivery["PERIOD_TEXT"];
    $delivery["PRICE_PERIOD_TEXT"] = $delivery["PRICE_PERIOD_TEXT"] .
        (!empty($delivery["PRICE_PERIOD_TEXT"]) ? ", " : "");
    if (empty($delivery["PRICE_FORMATED"]) || (int)$delivery["PRICE"] <= 0) {
        $delivery["PRICE_FORMATED"] = Loc::getMessage("FREE");
    }
    $delivery["PRICE_PERIOD_TEXT"] .= $delivery["PRICE_FORMATED"];
}
unset($delivery);

if ($arResult["USER_VALS"]["CONFIRM_ORDER"] == "Y") {
    $arResult["IS_CASH"] = !empty($arResult["ORDER"]) && !empty($arResult["PAY_SYSTEM"])
        && $arResult["PAY_SYSTEM"]["IS_CASH"] === "Y"
        && strripos($arResult["PAY_SYSTEM"]["ACTION_FILE"], "cash") !== false
        && !empty($arResult["PAY_SYSTEM"]["ACTION_FILE"]);
}
