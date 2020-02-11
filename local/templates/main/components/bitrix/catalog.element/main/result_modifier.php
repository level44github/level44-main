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

$arResult["MEASUREMENTS"] = \Level44\Base::getMultiLang(
    $arResult["DISPLAY_PROPERTIES"]["MEASUREMENTS"]["DISPLAY_VALUE"],
    $arResult["DISPLAY_PROPERTIES"]["MEASUREMENTS_EN"]["DISPLAY_VALUE"]
);


$APPLICATION->SetTitle($arResult["NAME"]);
