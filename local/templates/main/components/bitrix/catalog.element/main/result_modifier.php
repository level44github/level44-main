<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogElementComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

foreach ($arResult['SKU_PROPS'] as &$skuProp) {
    foreach ($skuProp['VALUES'] as &$value) {
        $value = $value["ID"] > 0 ? $value : null;
    }
    $skuProp["VALUES"] = array_filter($skuProp["VALUES"]);
    unset($value);
}
unset($skuProp);
