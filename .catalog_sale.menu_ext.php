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

$aMenuLinks = array_merge($aMenuLinks, Menu::getSaleChildren());