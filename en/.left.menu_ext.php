<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$aMenuLinksExt = [];

if (empty($aMenuLinks)) {
    $aMenuLinks = [];
}

$toCustomersMenuObj = new CMenu("to_customers");
$toCustomersMenuObj->Init(SITE_DIR);

$toCustomersMenu = array_map(fn($item) => [
    "TEXT"   => $item[0],
    "LINK"   => $item[1],
    "PARAMS" => $item[3],
], $toCustomersMenuObj->arMenu);

[$contactsMenu] = array_values(array_filter($toCustomersMenu, fn($item) => $item["PARAMS"]["IS_CONTACTS"]));
$toCustomersMenu = array_filter($toCustomersMenu, fn($item) => !$item["PARAMS"]["IS_CONTACTS"]);

$catalogMenuObj = new CMenu("catalog");
$catalogMenuObj->Init(SITE_DIR, true);

$catalogMenu = array_map(fn($item) => [
    "TEXT"   => $item[0],
    "LINK"   => $item[1],
    "PARAMS" => $item[3],
], $catalogMenuObj->arMenu);

[$saleMenu] = array_values(array_filter($catalogMenu, fn($item) => $item["PARAMS"]["IS_SALE"]));
$catalogMenu = array_filter($catalogMenu, fn($item) => !$item["PARAMS"]["IS_SALE"]);

$aMenuLinksExt = [
    [
        "Home",
        SITE_DIR,
        [],
        [],
        ""
    ],
    [
        "Catalog",
        "",
        [],
        [
            "SUBMENU" => $catalogMenu,
        ],
        ""
    ],
    [
        $saleMenu["TEXT"],
        $saleMenu["LINK"],
        [],
        [
            "CSS_CLASS" => "sale-section",
        ],
        !empty($saleMenu["LINK"]) ? 'true' : 'false'
    ],
    [
        "To buyers",
        "",
        [],
        [
            "SUBMENU" => $toCustomersMenu
        ],
        !empty($toCustomersMenu) ? 'true' : 'false'
    ],
    [
        $contactsMenu["TEXT"],
        $contactsMenu["LINK"],
        [],
        [],
        !empty($contactsMenu["LINK"]) ? 'true' : 'false'
    ]
];

$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);