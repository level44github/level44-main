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

foreach ($arResult["ITEMS"] as &$item) {
    $item["NAME"] = \Level44\Base::getMultiLang(
        $item["NAME"],
        $item["DISPLAY_PROPERTIES"]["NAME_EN"]["DISPLAY_VALUE"]
    );
}

unset($item);

$arResult["ITEMS"] = array_filter(
    $arResult["ITEMS"],
    function ($item) use ($arParams) {
        return (int)$arParams["RCM_PROD_ID"] !== (int)$item["ID"];
    }
);

$arResult = Content::setCatalogItemsEcommerceData($arResult);
