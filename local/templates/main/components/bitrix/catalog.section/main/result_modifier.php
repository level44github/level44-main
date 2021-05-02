<? use Level44\Content;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

$arResult = Content::setCatalogItemsEcommerceData($arResult);

foreach ($arResult["ITEMS"] as &$item) {
    $item["NAME"] = \Level44\Base::getMultiLang(
        $item["NAME"],
        $item["DISPLAY_PROPERTIES"]["NAME_EN"]["DISPLAY_VALUE"]
    );
}

unset($item);

$arResult["NAME"] = \Level44\Base::getMultiLang(
    $arResult["NAME"],
    $arResult["UF_NAME_EN"]
);

if (\Level44\Base::isEnLang()){
    $APPLICATION->SetTitle($arResult["NAME"]);
}
