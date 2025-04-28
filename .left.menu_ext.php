<?

use Level44\Menu;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$aMenuLinksExt = [];

if (empty($aMenuLinks)) {
    $aMenuLinks = [];
}

$toCustomersMenuObj = new CMenu("to_customers");
$toCustomersMenuObj->Init(SITE_DIR);

$toCustomersMenu = array_map(fn($item) => Menu::markIfSelected($item), $toCustomersMenuObj->arMenu);

$catalogMenuObj = new CMenu("catalog");
$catalogMenuObj->Init(SITE_DIR, true);


$aMenuLinksExt = array_merge(
    $catalogMenuObj->arMenu,
    [
        [
            "Покупателям",
            "",
            [],
            [
                "CHILDREN"        => $toCustomersMenu,
                "IS_TO_CUSTOMERS" => true
            ],
            !empty($toCustomersMenuObj->arMenu) ? 'true' : 'false'
        ],
    ]
);

$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);
Menu::setExpanded($aMenuLinks);