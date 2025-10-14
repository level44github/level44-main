<? use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var CMain $APPLICATION */
/** @var array $arResult */

$APPLICATION->AddViewContent("personal.back-link", $arResult['URL_TO_LIST']);
$APPLICATION->AddViewContent(
    "personal.navigation-title",
    Loc::getMessage('SPOD_ORDER_NUMBER_TEXT', ['#ORDER_NUMBER#' => $arResult["ACCOUNT_NUMBER"]])
);
$APPLICATION->AddViewContent(
    "personal.navigation-subtitle",
    Loc::getMessage("SPOD_ORDER_{$arResult["STATUS"]["ID"]}_STATUS") . ' ' . $arResult['DATE_INSERT_FORMATED']
);
?>