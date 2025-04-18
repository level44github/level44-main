<?

use Level44\Menu;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

if (empty($aMenuLinks)) {
    $aMenuLinks = [];
}

$aMenuLinks = array_map(fn($item) => Menu::markIfSelected($item), $aMenuLinks);
$aMenuLinks = [
    [
        "To buyers",
        "",
        [],
        [
            "CHILDREN"        => $aMenuLinks,
            "IS_TO_CUSTOMERS" => true
        ],
        !empty($aMenuLinks) ? 'true' : 'false'
    ],
];

Menu::setExpanded($aMenuLinks);