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
    if (!empty($item['OFFERS'])) {
        $item["ACTUAL_ITEM"] = isset($item['OFFERS'][$item['OFFERS_SELECTED']])
            ? $item['OFFERS'][$item['OFFERS_SELECTED']]
            : reset($item['OFFERS']);
    } else {
        $item["ACTUAL_ITEM"] = $item;
    }

    $item["ACTUAL_ITEM"]["PRICE"] = $item["ACTUAL_ITEM"]['ITEM_PRICES'][$item["ACTUAL_ITEM"]['ITEM_PRICE_SELECTED']];
    $item["NAME"] = \Level44\Base::getMultiLang(
        $item["NAME"],
        $item["DISPLAY_PROPERTIES"]["NAME_EN"]["DISPLAY_VALUE"]
    );
}

unset($item);
