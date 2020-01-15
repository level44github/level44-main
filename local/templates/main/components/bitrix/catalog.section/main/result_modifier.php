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
    $item["NAME"] = \Helper::isEnLang() && !empty($item["DISPLAY_PROPERTIES"]["NAME_EN"]["DISPLAY_VALUE"])
        ? $item["DISPLAY_PROPERTIES"]["NAME_EN"]["DISPLAY_VALUE"] : $item["NAME"];
}

unset($item);
