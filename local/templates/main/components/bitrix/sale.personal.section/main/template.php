<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
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

if ($arParams["MAIN_CHAIN_NAME"] !== '')
{
    $APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}

$APPLICATION->AddViewContent("personal.back-link", SITE_DIR);
$APPLICATION->AddViewContent("personal.navigation-title", Loc::getMessage('SPS_TITLE_MAIN'));

$arIds = [];
if (\Bitrix\Main\Loader::includeModule('awelite.favorite')) {
    $objFavCookies = new \Awelite\Favorite\Cookies();
    $arIds = $objFavCookies->getIds();
}

$APPLICATION->IncludeComponent(
    "bitrix:menu",
    "personal_mobile",
    [
        "ROOT_MENU_TYPE"        => "left",
        "MAX_LEVEL"             => "1",
        "CHILD_MENU_TYPE"       => "top",
        "USE_EXT"               => "N",
        "DELAY"                 => "N",
        "ALLOW_MULTI_SELECT"    => "N",
        "MENU_CACHE_TYPE"       => "A",
        "MENU_CACHE_TIME"       => "3600",
        "MENU_CACHE_USE_GROUPS" => "Y",
        "MENU_CACHE_GET_VARS"   => "",
        "COUNT" => count($arIds)
    ]
);

$_REQUEST['show_all'] = 'Y';
?>
<div class="d-none d-md-block">
    <?
    $APPLICATION->IncludeComponent(
        "bitrix:sale.personal.order.list",
        "",
        array(
            "PATH_TO_DETAIL" => $arResult["PATH_TO_ORDER_DETAIL"],
            "PATH_TO_CANCEL" => $arResult["PATH_TO_ORDER_CANCEL"],
            "PATH_TO_CATALOG" => $arParams["PATH_TO_CATALOG"],
            "PATH_TO_COPY" => $arResult["PATH_TO_ORDER_COPY"],
            "PATH_TO_BASKET" => $arParams["PATH_TO_BASKET"],
            "PATH_TO_PAYMENT" => $arParams["PATH_TO_PAYMENT"],
            "SAVE_IN_SESSION" => $arParams["SAVE_IN_SESSION"],
            "ORDERS_PER_PAGE" => $arParams["ORDERS_PER_PAGE"],
            "SET_TITLE" =>'N',
            "ID" => $arResult["VARIABLES"]["ID"],
            "NAV_TEMPLATE" => $arParams["NAV_TEMPLATE"],
            "ACTIVE_DATE_FORMAT" => $arParams["ACTIVE_DATE_FORMAT"],
            "HISTORIC_STATUSES" => $arParams["ORDER_HISTORIC_STATUSES"],
            "ALLOW_INNER" => $arParams["ALLOW_INNER"],
            "ONLY_INNER_FULL" => $arParams["ONLY_INNER_FULL"],
            "CACHE_TYPE" => $arParams["CACHE_TYPE"],
            "CACHE_TIME" => $arParams["CACHE_TIME"],
            "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
            "DEFAULT_SORT" => $arParams["ORDER_DEFAULT_SORT"],
            "DISALLOW_CANCEL" => $arParams["ORDER_DISALLOW_CANCEL"],
            "RESTRICT_CHANGE_PAYSYSTEM" => $arParams["ORDER_RESTRICT_CHANGE_PAYSYSTEM"],
            "REFRESH_PRICES" => $arParams["ORDER_REFRESH_PRICES"],
            "CONTEXT_SITE_ID" => $arParams["CONTEXT_SITE_ID"],
            "AUTH_FORM_IN_TEMPLATE" => 'Y',
        ),
        $component
    );?>
</div>