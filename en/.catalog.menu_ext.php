<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;
$aMenuLinksExt = array();

if (empty($aMenuLinks)) {
    $aMenuLinks = [];
}

$aMenuLinks[] = [
    "All",
    SITE_DIR . "catalog/",
    Array(),
    Array(),
    ""
];


if (CModule::IncludeModule('iblock')) {
    $arFilter = array(
        "TYPE" => "catalog",
        "SITE_ID" => SITE_ID,
    );

    $dbIBlock = CIBlock::GetList(array('SORT' => 'ASC', 'ID' => 'ASC'), $arFilter);
    $dbIBlock = new CIBlockResult($dbIBlock);

    if ($arIBlock = $dbIBlock->GetNext()) {
        if ($arIBlock["ACTIVE"] == "Y") {
            $aMenuLinksExt = $APPLICATION->IncludeComponent(
                "level44:menu.sections",
                "",
                array(
                    "IS_SEF" => "Y",
                    "SEF_BASE_URL" => "",
                    "SECTION_PAGE_URL" => $arIBlock['SECTION_PAGE_URL'],
                    "DETAIL_PAGE_URL" => $arIBlock['DETAIL_PAGE_URL'],
                    "IBLOCK_TYPE" => $arIBlock['IBLOCK_TYPE_ID'],
                    "IBLOCK_ID" => $arIBlock['ID'],
                    "DEPTH_LEVEL" => "3",
                    "CACHE_TYPE" => "N",
                ),
                false,
                Array('HIDE_ICONS' => 'Y')
            );
        }
    }
}

$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);
