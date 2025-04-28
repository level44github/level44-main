<? use Level44\Content;
use Level44\Base;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 */

foreach ($arResult["ITEMS"] as &$item) {
    $previewImages = $item["DISPLAY_PROPERTIES"]["MORE_PHOTO"]["FILE_VALUE"];

    if (!is_array($previewImages)) {
        $previewImages = [];
    }

    if (!empty($previewImages) && !$previewImages[0]) {
        $previewImages = [$previewImages];
    }

    $previewImages = array_map(fn($previewImage) => $previewImage['SRC'], $previewImages);

    $item["PREVIEW_IMAGES"] = array_splice($previewImages, 0, 1);
}

unset($item);

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

$arResult = Content::setCatalogItemsEcommerceData($arResult);

foreach ($arResult["ITEMS"] as &$item) {
    if ($item["PREVIEW_PICTURE"]["SRC"]) {
        array_unshift($item["PREVIEW_IMAGES"], $item["PREVIEW_PICTURE"]["SRC"]);
    }

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

if (\Level44\Base::isEnLang()) {
    $APPLICATION->SetTitle($arResult["NAME"]);
}

//Multi lang breadcrumb
$paths = [];
$pathIterator = CIBlockSection::GetNavChain(
    $arResult['IBLOCK_ID'],
    $arResult['ID'],
    [
        'ID',
        'CODE',
        'IBLOCK_ID',
        'NAME',
        'SECTION_PAGE_URL'
    ]
);
$pathIterator->SetUrlTemplates('', $arParams['SECTION_URL']);
while ($path = $pathIterator->GetNext()) {
    $paths[] = $path;
}

$sectionIds = array_map(fn($item) => (int)$item['ID'], $paths);
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

foreach ($paths as $path) {
    $APPLICATION->AddChainItem(
        Base::getMultiLang($path['NAME'], $enSectionNames[$path['ID']]),
        $path['~SECTION_PAGE_URL']
    );
}
