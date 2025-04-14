<?

use Level44\Menu;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;
$aMenuLinksExt = [];

if (empty($aMenuLinks)) {
    $aMenuLinks = [];
}

if (CModule::IncludeModule('iblock')) {
    $arFilter = [
        "TYPE"    => "catalog",
        "SITE_ID" => SITE_ID,
    ];

    $dbIBlock = CIBlock::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], $arFilter);
    $dbIBlock = new CIBlockResult($dbIBlock);

    if ($arIBlock = $dbIBlock->GetNext()) {
        if ($arIBlock["ACTIVE"] == "Y") {
            $aMenuLinksExt = $APPLICATION->IncludeComponent(
                "level44:menu.sections",
                "",
                [
                    "IS_SEF"           => "Y",
                    "SEF_BASE_URL"     => "",
                    "SECTION_PAGE_URL" => $arIBlock['SECTION_PAGE_URL'],
                    "DETAIL_PAGE_URL"  => $arIBlock['DETAIL_PAGE_URL'],
                    "IBLOCK_TYPE"      => $arIBlock['IBLOCK_TYPE_ID'],
                    "IBLOCK_ID"        => $arIBlock['ID'],
                    "DEPTH_LEVEL"      => "3",
                    "CACHE_TYPE"       => "N",
                    "SALE_FILTER"      => "N",
                ],
                false,
                ['HIDE_ICONS' => 'Y']
            );
        }
    }
}

$aMenuLinksExt = Menu::prepareMenuSections($aMenuLinksExt);
$aMenuLinksExt = Menu::addSaleSection($aMenuLinksExt);

$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);
Menu::setExpanded($aMenuLinks);