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


$aMenuLinksExt = [
    [
        "Главная",
        SITE_DIR,
        [],
        [],
        ""
    ],
];

$aMenuLinksExt = array_merge($aMenuLinksExt,
    $catalogMenuObj->arMenu,
    [
        [
            "",
            "",
            [],
            [
            ],
            !empty($toCustomersMenu) || !empty($contactsMenu["LINK"]) ? 'true' : 'false'
        ],
        [
            "Покупателям",
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
    ]
);

$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);