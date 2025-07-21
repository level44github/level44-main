<? use Level44\Base;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
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

if (!empty($arResult["DETAIL_TEXT"])) {
    $arResult["DETAIL_TEXT"] = preg_replace('#(\s*<br\s*/?>)*\s*$#i', '', $arResult["DETAIL_TEXT"]);
}

$arResult["DETAIL_TEXT_TYPE"] = \Level44\Base::getMultiLang(
    $arResult["DETAIL_TEXT_TYPE"],
    strtolower($arResult["DISPLAY_PROPERTIES"]["DETAIL_TEXT_EN"]["VALUE"]["TYPE"])
);

$arResult['ARTNUMBER'] = $arResult["DISPLAY_PROPERTIES"]['ARTNUMBER']['DISPLAY_VALUE'];

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
    $arResult["DISPLAY_PROPERTIES"]["MEASUREMENTS_KIT"]["DISPLAY_VALUE"],
    $arResult["DISPLAY_PROPERTIES"]["MEASUREMENTS_KIT_EN"]["DISPLAY_VALUE"]
);

 if (!empty($arResult["MEASUREMENTS"])){
     $arResult["MEASUREMENTS"] = preg_replace('#(\s*<br\s*/?>)*\s*$#i', '', $arResult["MEASUREMENTS"]);
 }

$arResult["CARE_INFO"] = \Level44\Base::getMultiLang(
    $arResult["DISPLAY_PROPERTIES"]["CARE_INFO"]["DISPLAY_VALUE"],
    $arResult["DISPLAY_PROPERTIES"]["CARE_INFO_EN"]["DISPLAY_VALUE"]
);

$arResult["PRODUCT_COMPOSITION"] = \Level44\Base::getMultiLang(
    $arResult["DISPLAY_PROPERTIES"]["PRODUCT_COMPOSITION"]["DISPLAY_VALUE"],
    $arResult["DISPLAY_PROPERTIES"]["EN_PRODUCT_COMPOSITION"]["DISPLAY_VALUE"]
);

$APPLICATION->SetTitle($arResult["NAME"]);

$sizes = [];
$arResult['STORES'] = [];
$sizeRefProp = $arResult['SKU_PROPS']['SIZE_REF'];

foreach ($sizeRefProp['VALUES'] as $sizeRefValue) {
    foreach ($arResult['OFFERS'] as $offer) {
        if ($offer['TREE']["PROP_{$sizeRefProp['ID']}"] === $sizeRefValue['ID']) {
            $sizes[] = [
                'OFFER_ID' => $offer['ID'],
                'NAME'     => $sizeRefValue['NAME'],
            ];
        }
    }
}

if (!empty($sizes)) {
    $storesRests = \Bitrix\Sale\StoreProductTable::getList(
        [
            'filter' => [
                'PRODUCT_ID' => array_map(fn($size) => $size['OFFER_ID'], $sizes)
            ]
        ])->fetchAll();

    $rsStores = \Bitrix\Catalog\StoreTable::getList([
        'filter' => ['ACTIVE' => 'Y', 'ISSUING_CENTER' => 'Y'],
        'select' => ['ID', 'TITLE', 'ADDRESS', 'UF_*']
    ]);

    $stores = [];
    while ($store = $rsStores->fetch()) {
        $stores[$store['ID']] = $store;
    }

    foreach ($storesRests as $storeRests) {
        if ((int)$storeRests['AMOUNT'] > 0) {
            $size = current(array_filter($sizes, fn($size) => (int)$size['OFFER_ID'] === (int)$storeRests['PRODUCT_ID']));
            if (!empty($size)) {
                $availability[$storeRests['STORE_ID']][] = $size;
            }
        }
    }

    foreach ($availability as $storeId => $sizes) {
        if (!empty($store = $stores[$storeId])) {
            $arResult['STORES'][] = [
                'TITLE'   => Base::getMultiLang($store['TITLE'], $store['UF_TITLE_EN']),
                'ADDRESS' => Base::getMultiLang($store['ADDRESS'], $store['UF_ADDRESS_EN']),
                'SIZES'   => $sizes
            ];
        }
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

if (!empty($arResult["PROPERTIES"]["VIDEO"]['VALUE']) && !empty($arResult["PROPERTIES"]["PREVIEW_VIDEO"]['VALUE'])) {
    $actualItem["MORE_PHOTO"][] = [
        'SRC'        => \CFile::GetPath($arResult["PROPERTIES"]["VIDEO"]['VALUE']),
        'POSTER_SRC' => \CFile::GetPath($arResult["PROPERTIES"]["PREVIEW_VIDEO"]['VALUE']),
        'IS_VIDEO'   => true,
    ];
}

$rsSections = \CIBlockElement::GetElementGroups($arResult["ID"]);

$sections = [];
while ($section = $rsSections->GetNext()) {
    $sections[] = $section;
}

$arResult["IS_SHOES"] = !empty(array_filter($sections, fn($section) => $section["CODE"] === 'obuv'));

$arResult["ACTUAL_ITEM"] = $actualItem;

$component->SetResultCacheKeys(["ACTUAL_ITEM", "IS_SHOES"]);

if (!empty($arResult['SECTION']['PATH']) && is_array($arResult['SECTION']['PATH'])) {
    $sectionIds = array_map(fn($item) => (int)$item['ID'], $arResult['SECTION']['PATH']);
    $enSectionNames = [];

    if (!empty($sectionIds)) {
        $rsSections = CIBlockSection::GetList([], [
            'ID'        => $sectionIds,
            'IBLOCK_ID' => $arResult['IBLOCK_ID'],
        ], false, [
            "ID",
            "UF_NAME_EN",
            "CODE",
        ]);

        while ($section = $rsSections->GetNext()) {
            $enSectionNames[$section['ID']] = $section['UF_NAME_EN'];
        }
    }

    foreach ($arResult['SECTION']['PATH'] as $path) {
        $APPLICATION->AddChainItem(
            Base::getMultiLang($path['NAME'], $enSectionNames[$path['ID']]),
            $path['~SECTION_PAGE_URL']
        );
    }

    $APPLICATION->AddChainItem($arResult["NAME"]);
}