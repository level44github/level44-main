<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;
use \Level44\Base;
use Level44\Product;

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$columns = [];
$fulls = [];

foreach ($arResult["JS_DATA"]["ORDER_PROP"]["properties"] as &$prop) {
    $prop["REQUIRED"] = $prop["REQUIRED"] === "Y";

    $prop["VALUE"] = reset($prop["VALUE"]);
    if ($prop["CODE"] === "LOCATION") {
        $prop["VALUE"] = \CSaleLocation::getLocationIDbyCODE($prop["VALUE"]);
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

    if ($prop["CODE"] === "LOCATION") {
        if (empty($prop["VALUE"])) {
            $arResult["DELIVERY"] = [];
            $arResult["PAY_SYSTEM"] = [];
        }

        $locPath = [];
        if ((int)$prop["VALUE"] > 0) {
            $locPath = \Bitrix\Sale\Location\LocationTable::getPathToNode($prop["VALUE"], [])->fetchAll();
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
            $prop["NAME"] = \Level44\Base::getMultiLang("Страна", "Country");
        }
    }

    if (in_array($prop["CODE"], ["EMAIL", "PHONE"])) {
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

$productList = \CCatalogSKU::getProductList($basketProductIds);

$productIds = [];
foreach ($productList as $offerId => $product) {
    $productIds[$offerId] = $product["ID"];
}

$productsData = $productIds;

$rsProductsData = \CIBlockElement::GetList(
    [],
    [
        "ID" => array_values($productsData),
        "IBLOCK_ID" => \Level44\Base::CATALOG_IBLOCK_ID
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

\Level44\Base::setColorOffers($products);

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
        $itemPriceDollar = \Level44\Base::getDollarPrice(
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

    $basketItem["PRICE_DOLLAR"] = \Level44\Base::isEnLang() ? \Level44\Base::formatDollar($itemPriceDollar) : false;

    $basketItem["NAME"] = \Level44\Base::getMultiLang(
        $basketItem["NAME"],
        $arProductsAdd[$basketItem["PRODUCT_ID"]]["NAME_EN"]
    );

    if (!empty($basketItem["COLOR"])) {
        $basketItem["PROPS"]["COLOR_REF"] = [
            "CODE" => "COLOR_REF",
            "VALUE" => $basketItem["COLOR"]["COLOR_NAME"],
        ];
    }

    unset($prop);
}
unset($basketItem);

$arResult["BASKET_ITEMS_QUANTITY"] = $basketItemsQuantity;
$arResult["SUM_PRICE_DOLLAR"] = \Level44\Base::isEnLang() ? \Level44\Base::formatDollar($sumPriceDollar) : false;
$arResult["OLD_SUM_PRICE"] = CCurrencyLang::CurrencyFormat($oldSumPrice, "RUB");
$arResult["OLD_SUM_PRICE_DOLLAR"] = Base::formatDollar($oldSumPriceDollar);
$arResult["SHOW_OLD_SUM_PRICE"] = !empty($oldSumPrice) && $oldSumPrice !== $arResult["ORDER_PRICE"];

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

    if ($delivery["NAME"] === "DHL") {
        if (strripos($delivery["PERIOD_TEXT"], "Приблизительное число дней доставки: ") !== false) {
            $delivery["PERIOD_TEXT"] = str_replace("Приблизительное число дней доставки: ", "", $delivery["PERIOD_TEXT"]);
        }
        $delivery["PERIOD_TEXT"] = $delivery["PERIOD_TEXT"] . " " . Base::getMultiLang("дн.", "dd.");
    }


    $delivery["PRICE_PERIOD_TEXT"] = $delivery["PERIOD_TEXT"];
    $delivery["PRICE_PERIOD_TEXT"] = $delivery["PRICE_PERIOD_TEXT"] .
        (!empty($delivery["PRICE_PERIOD_TEXT"]) ? ", " : "");

    $delivery["DOLLAR_PRICE"] = \Level44\Base::getDollarPrice($delivery["PRICE"]);
    if (empty($delivery["PRICE_FORMATED"]) || (int)$delivery["PRICE"] <= 0) {
        if (!empty($delivery["CALCULATE_ERRORS"])) {
            $delivery["CALCULATE_INVALID"] = true;
            $delivery["CHECKED"] = false;
        } else {
            $delivery["PRICE_FORMATED"] = Loc::getMessage("FREE");
            $delivery["DOLLAR_PRICE"] = false;
        }
    }
    $delivery["PRICE_PERIOD_TEXT"] .= $delivery["PRICE_FORMATED"];
    if ($delivery["CHECKED"]) {
        $arResult["CURRENT_DELIVERY"] = $delivery;
    }
}
unset($delivery);
foreach ($arResult["PAY_SYSTEM"] as &$paySystem) {
    if (!is_array($paySystem)) {
        continue;
    }

    if ($paySystem["PSA_ACTION_FILE"] === "yandexcheckoutcustom") {
        $paySystem["ICONS"] = [
            "visa",
            "mastercard",
            "maestro",
            "mir",
            "jcb",
            "diners-club",
            "american-express",
            "qiwi",
            "ya-money",
        ];
    }

    if (strripos($paySystem["PSA_ACTION_FILE"], "vampirus.yandexcustom") !== false) {
        $paySystem["ICONS"] = [
            "visa",
            "mastercard",
            "maestro",
            "mir",
            "qiwi",
            "ya-money",
        ];
    }

}
unset($paySystem);

if ($arResult["USER_VALS"]["CONFIRM_ORDER"] == "Y") {
    $arResult["IS_CASH"] = !empty($arResult["ORDER"]) && !empty($arResult["PAY_SYSTEM"])
        && $arResult["PAY_SYSTEM"]["IS_CASH"] === "Y"
        && strripos($arResult["PAY_SYSTEM"]["ACTION_FILE"], "cash") !== false
        && !empty($arResult["PAY_SYSTEM"]["ACTION_FILE"]);
}

$dollarTotalPrice = \Level44\Base::getDollarPrice($arResult["JS_DATA"]["TOTAL"]["DELIVERY_PRICE"], null, true) + $sumPriceDollar;
$arResult["ORDER_TOTAL_PRICE_DOLLAR"] = $dollarTotalPrice <= 0 || !\Level44\Base::isEnLang() ? false
    : \Level44\Base::formatDollar($dollarTotalPrice);
$arResult["ORDER_TOTAL_PRICE"] = $arResult["JS_DATA"]["TOTAL"]["ORDER_TOTAL_PRICE_FORMATED"];