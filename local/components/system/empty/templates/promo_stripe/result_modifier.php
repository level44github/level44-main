<?php

$isEn = (defined('SITE_ID') && SITE_ID === 'en');

$res = \CIBlockElement::GetList(
    ['SORT' => 'ASC', 'ID' => 'ASC'],
    [
        'IBLOCK_ID' => 7,
        'ACTIVE'    => 'Y',
    ],
    false,
    false,
    [
        'ID',
        'NAME',
        'PROPERTY_LINK_TEXT',
        'PROPERTY_LINK_TEXT_ENG',
        'PROPERTY_LINK',
        'PROPERTY_COLOR_FON',
        'PROPERTY_COLOR_TEXT',
        'PROPERTY_TEXT_ENG',
    ]
);

$arResult['ITEMS'] = [];
while ($item = $res->GetNext()) {
    $linkUrl = (string)($item['PROPERTY_LINK_VALUE'] ?? '');
    if ($linkUrl !== '' && $isEn) {
        $linkUrl = '/en/' . ltrim($linkUrl, '/');
    }
    $arResult['ITEMS'][] = [
        'text'       => $isEn
            ? (string)($item['PROPERTY_TEXT_ENG_VALUE'] ?? $item['NAME'] ?? '')
            : (string)($item['NAME'] ?? ''),
        'link_text'  => $isEn
            ? (string)($item['PROPERTY_LINK_TEXT_ENG_VALUE'] ?? $item['PROPERTY_LINK_TEXT_VALUE'] ?? '')
            : (string)($item['PROPERTY_LINK_TEXT_VALUE'] ?? ''),
        'link_url'   => $linkUrl,
        'color_fon'  => (string)($item['PROPERTY_COLOR_FON_VALUE'] ?? '#f5f5f5'),
        'color_text' => (string)($item['PROPERTY_COLOR_TEXT_VALUE'] ?? '#333333'),
    ];
}
