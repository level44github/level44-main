<?php

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
        'PROPERTY_LINK',
        'PROPERTY_COLOR_FON',
        'PROPERTY_COLOR_TEXT',
    ]
);

$arResult['ITEMS'] = [];
while ($item = $res->GetNext()) {
    $arResult['ITEMS'][] = [
        'text'       => (string)($item['NAME'] ?? ''),
        'link_text'  => (string)($item['PROPERTY_LINK_TEXT_VALUE'] ?? ''),
        'link_url'   => (string)($item['PROPERTY_LINK_VALUE'] ?? ''),
        'color_fon'  => (string)($item['PROPERTY_COLOR_FON_VALUE'] ?? '#f5f5f5'),
        'color_text' => (string)($item['PROPERTY_COLOR_TEXT_VALUE'] ?? '#333333'),
    ];
}
