<?php

use Level44\Base;

$res = \CIBlockElement::GetList(
    [
        "SORT" => "ASC"
    ],
    [
        "IBLOCK_ID" => Base::BANNER_SLIDES_IBLOCK_ID,
        "ACTIVE"    => "Y"
    ],
    false,
    false,
    [
        "ID",
        "IBLOCK_ID",
        "PROPERTY_FILE_MOBILE",
        "PROPERTY_FILE_DESKTOP",
        "PROPERTY_TITLE",
        "PROPERTY_TEXT",
        "PROPERTY_LINK_TEXT",
        "PROPERTY_LINK_SECTION",
        "PROPERTY_IS_SALE_SECTION",
        "PROPERTY_TITLE_EN",
        "PROPERTY_TEXT_EN",
        "PROPERTY_LINK_TEXT_EN",
        "PROPERTY_LINK_ADDRESS",
        "PROPERTY_SPLIT_FILE_1",
        "PROPERTY_SPLIT_FILE_2",
    ]
);

$items = [];
while ($item = $res->GetNext()) {
    $items[] = $item;
}

$slides = [];
foreach ($items as $item) {
    $fileMobileSrc = \CFile::GetPath($item["PROPERTY_FILE_MOBILE_VALUE"]);
    $fileDesktopSrc = \CFile::GetPath($item["PROPERTY_FILE_DESKTOP_VALUE"]);
    $split1fileDesktopSrc = \CFile::GetPath($item["PROPERTY_SPLIT_FILE_1_VALUE"]);
    $split2fileDesktopSrc = \CFile::GetPath($item["PROPERTY_SPLIT_FILE_2_VALUE"]);

    $mobileFile = [
        'src'     => $fileMobileSrc,
        'isVideo' => (bool)preg_match('/\.(mpg|avi|wmv|mpeg|mpe|flv|mp4)$/i', $fileMobileSrc),

    ];

    $desktopFile = [];

    if (!empty($fileDesktopSrc)) {
        $desktopFile['single'] = [
            'src'     => $fileDesktopSrc,
            'isVideo' => (bool)preg_match('/\.(mpg|avi|wmv|mpeg|mpe|flv|mp4)$/i', $fileDesktopSrc),
        ];
    }

    if (!empty($split1fileDesktopSrc) && !empty($split2fileDesktopSrc)) {
        $desktopFile['split'] = [
            [
                'src' => $split1fileDesktopSrc,
            ],
            [
                'src' => $split2fileDesktopSrc,
            ],
        ];
    }

    $link = '';

    if (!empty($item['PROPERTY_LINK_ADDRESS_VALUE'])) {
        $link = SITE_DIR . $item['PROPERTY_LINK_ADDRESS_VALUE'];

    } elseif ($item['PROPERTY_LINK_SECTION_VALUE']) {
        $linkSection = \CIBlockSection::GetList(
            [],
            [
                "ID"     => $item['PROPERTY_LINK_SECTION_VALUE'],
                "ACTIVE" => "Y"
            ],
            false,
            [
                "ID",
                "SECTION_PAGE_URL"
            ]
        )->GetNext();

        if (!empty($linkSection)) {
            $link = $linkSection['SECTION_PAGE_URL'];
            if ($item['PROPERTY_IS_SALE_SECTION_VALUE'] === 'Y') {
                $link = str_replace('/catalog', '/catalog/sale', $link);
            }
        }
    }


    $arResult['SLIDES'][] = [
        'files' => [
            'mobile'  => $mobileFile,
            'desktop' => $desktopFile,
        ],
        'title' => (string)Base::getMultiLang($item['PROPERTY_TITLE_VALUE'], $item['PROPERTY_TITLE_EN_VALUE']),
        'text'  => (string)Base::getMultiLang($item['PROPERTY_TEXT_VALUE'], $item['PROPERTY_TEXT_EN_VALUE']),
        'link'  => [
            'text'    => (string)Base::getMultiLang($item["PROPERTY_LINK_TEXT_VALUE"], $item["PROPERTY_LINK_TEXT_EN_VALUE"]),
            'address' => $link,
        ],
    ];
}