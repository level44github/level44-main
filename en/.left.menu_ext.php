<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$aMenuLinksExt = [];

if (empty($aMenuLinks)) {
    $aMenuLinks = [];
}

$toCustomersMenuObj = new CMenu("to_customers");
$toCustomersMenuObj->Init(SITE_DIR, true);

$catalogMenuObj = new CMenu("catalog");
$catalogMenuObj->Init(SITE_DIR, true);

$aMenuLinks = array_merge($aMenuLinks, $catalogMenuObj->arMenu, $toCustomersMenuObj->arMenu);