<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogElementComponent $component
 */


\Bitrix\Main\Loader::includeModule("highloadblock");

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

\Level44\Base::setOriginalMorePhoto($arResult["MORE_PHOTO"]);

foreach ($arResult['OFFERS'] as &$offer) {
   \Level44\Base::setOriginalMorePhoto($offer["MORE_PHOTO"]);
}
unset($offer);

$arResult["NAME"] = \Level44\Base::getMultiLang(
    $arResult["NAME"],
    $arResult["DISPLAY_PROPERTIES"]["NAME_EN"]["DISPLAY_VALUE"]
);

$arResult["DETAIL_TEXT"] = \Level44\Base::getMultiLang(
    $arResult["DETAIL_TEXT"],
    $arResult["DISPLAY_PROPERTIES"]["DETAIL_TEXT_EN"]["DISPLAY_VALUE"]
);

foreach ($arResult['SKU_PROPS'] as &$skuProp) {
    foreach ($skuProp['VALUES'] as &$value) {
        $value = $value["ID"] > 0 ? $value : null;
    }
    $skuProp["VALUES"] = array_filter($skuProp["VALUES"]);
    unset($value);
}
unset($skuProp);

$productProperties = [];

foreach ($arResult["PROPERTIES"] as $pid => $arProperty) {
    if (strripos($arProperty["CODE"], \Level44\Base::isEnLang() ? "EN_PRODUCT_" : "PRODUCT_") === 0) {
        $productProp = \CIBlockFormatProperties::GetDisplayValue($arResult, $arProperty, "");
        if (!empty($productProp["DISPLAY_VALUE"]) && $productProp["PROPERTY_TYPE"] === "S") {
            $productProperties[] = $productProp;
        }
    }
};

$arResult["PRODUCT_PROPERTIES"] = $productProperties;


$linkedElements = $arResult["DISPLAY_PROPERTIES"]["OTHER_COLORS"]["LINK_ELEMENT_VALUE"];

\Level44\Base::setColorOffers($linkedElements, $arResult);

$arResult["COLORS"] = $linkedElements;

$arResult["MEASUREMENTS_ROWS"] = [];

$arResult["MEASUREMENTS"] = \Level44\Base::getMultiLang(
    $arResult["DISPLAY_PROPERTIES"]["MEASUREMENTS_KIT"]["DISPLAY_VALUE"],
    $arResult["DISPLAY_PROPERTIES"]["MEASUREMENTS_KIT_EN"]["DISPLAY_VALUE"]
);

if (empty($arResult["MEASUREMENTS"])){
    $arResult["MEASUREMENTS_ROWS"] = \Level44\Base::getMultiLang(
        $arResult["DISPLAY_PROPERTIES"]["MEASUREMENTS"]["DISPLAY_VALUE"],
        $arResult["DISPLAY_PROPERTIES"]["MEASUREMENTS_EN"]["DISPLAY_VALUE"]
    );
}

$APPLICATION->SetTitle($arResult["NAME"]);

$videoProperty = $arResult["DISPLAY_PROPERTIES"]["VIDEO"];
$arResult["VIDEOS"] = [];

if (is_array($videoProperty["VALUE"])) {
    foreach ($videoProperty["VALUE"] as $key => $video) {
        if (!$video["path"] || !$videoProperty["PROPERTY_VALUE_ID"][$key]) {
            continue;
        }

        $arResult["VIDEOS"][] = [
            "PATH" => $video["path"],
            "ID"   => $videoProperty["PROPERTY_VALUE_ID"][$key],
        ];
    }
}

if (!empty($arResult['OFFERS'])) {
    foreach ($arResult["OFFERS"] as &$offer) {
        $offer["MORE_PHOTO"] = array_filter($offer["MORE_PHOTO"], function ($item) {
            return (bool)$item && is_array($item);
        });

        $offer["MORE_PHOTO"] = is_array($offer["MORE_PHOTO"]) ? $offer["MORE_PHOTO"] : [];
        $offer["MORE_PHOTO_COUNT"] = count($offer["MORE_PHOTO"]);
    }
    unset($offer);

    foreach ($arResult['JS_OFFERS'] as &$offer) {
        $offer["SLIDER"] = array_filter($offer["SLIDER"], function ($item) {
            return (bool)$item && is_array($item);
        });

        $offer["SLIDER"] = is_array($offer["SLIDER"]) ? $offer["SLIDER"] : [];
        $offer["SLIDER_COUNT"] = count($offer["SLIDER"]);
    }

    unset($offer);

    $actualItem = isset($arResult['OFFERS'][$arResult['OFFERS_SELECTED']])
        ? $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]
        : reset($arResult['OFFERS']);
    $showSliderControls = false;
} else {
    $actualItem = $arResult;
}

$arResult["ACTUAL_ITEM"] = $actualItem;

$component->SetResultCacheKeys(["ACTUAL_ITEM"]);