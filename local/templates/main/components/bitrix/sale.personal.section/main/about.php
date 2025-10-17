<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponent $component */

/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;

global $USER;
if (!$USER->IsAuthorized()) {
    LocalRedirect($arResult['LINK_TO_LOGIN']);
}

if ($arParams["MAIN_CHAIN_NAME"] !== '') {
    $APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}

$APPLICATION->AddChainItem('Система привелегий');

//$APPLICATION->AddViewContent("personal.back-link", $arResult['SEF_FOLDER']);
//$APPLICATION->AddViewContent("personal.navigation-title", 'Система привелегий');



?>

    <div class="profile profile-orders">

        <p class="about-loyalty-text">
            <h3 style="text-align:center;">Система привилегий</h3>
        <?$APPLICATION->IncludeComponent(
            "bitrix:news.detail",
            "material-info",
            Array(
                "ACTIVE_DATE_FORMAT" => "d.m.Y",
                "ADD_ELEMENT_CHAIN" => "N",
                "ADD_SECTIONS_CHAIN" => "Y",
                "AJAX_MODE" => "N",
                "AJAX_OPTION_ADDITIONAL" => "",
                "AJAX_OPTION_HISTORY" => "N",
                "AJAX_OPTION_JUMP" => "N",
                "AJAX_OPTION_STYLE" => "Y",
                "BROWSER_TITLE" => "-",
                "CACHE_GROUPS" => "Y",
                "CACHE_TIME" => "36000000",
                "CACHE_TYPE" => "A",
                "CHECK_DATES" => "Y",
                "DETAIL_URL" => "",
                "DISPLAY_BOTTOM_PAGER" => "Y",
                "DISPLAY_DATE" => "Y",
                "DISPLAY_NAME" => "Y",
                "DISPLAY_PICTURE" => "Y",
                "DISPLAY_PREVIEW_TEXT" => "Y",
                "DISPLAY_TOP_PAGER" => "N",
                "ELEMENT_CODE" => "",
                "ELEMENT_ID" => '6925',
                "FIELD_CODE" => array("",""),
                "IBLOCK_ID" => "6",
                "IBLOCK_TYPE" => "content",
                "IBLOCK_URL" => "",
                "INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
                "MESSAGE_404" => "",
                "META_DESCRIPTION" => "-",
                "META_KEYWORDS" => "-",
                "PAGER_BASE_LINK_ENABLE" => "N",
                "PAGER_SHOW_ALL" => "N",
                "PAGER_TEMPLATE" => ".default",
                "PAGER_TITLE" => "Страница",
                "PROPERTY_CODE" => array("content","content_eng"),
                "SET_BROWSER_TITLE" => "Y",
                "SET_CANONICAL_URL" => "N",
                "SET_LAST_MODIFIED" => "N",
                "SET_META_DESCRIPTION" => "Y",
                "SET_META_KEYWORDS" => "Y",
                "SET_STATUS_404" => "N",
                "SET_TITLE" => "Y",
                "SHOW_404" => "N",
                "STRICT_SECTION_CHECK" => "N",
                "USE_PERMISSIONS" => "N",
                "USE_SHARE" => "N"
            )
        );?>
        </p>
    </div>
<?php
