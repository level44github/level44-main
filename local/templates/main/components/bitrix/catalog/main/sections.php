<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$this->setFrameMode(true);

$section = \CIBlockSection::GetList(
    [
        "SORT" => "ASC"
    ],
    [
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
        "ACTIVE" => "Y",
        "DEPTH_LEVEL " => 1,
    ],
    false,
    [
        "ID",
        "SECTION_PAGE_URL"
    ],
    [
        "nTopCount" => 1
    ]
)->GetNext();

if (!$section || !$section["SECTION_PAGE_URL"]) {
    LocalRedirect(SITE_DIR);
}

LocalRedirect($section["SECTION_PAGE_URL"]);
