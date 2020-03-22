<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
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
    $item["PRICE_DOLLAR"] = \Level44\Base::getDollarPrice($item["DISPLAY_PROPERTIES"]['PRICE_DOLLAR']["DISPLAY_VALUE"]);
}

unset($item);
