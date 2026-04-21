<?php

use Level44\Base;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

foreach ($arResult['ITEMS'] as &$item) {
    $previewImages = $item['DISPLAY_PROPERTIES']['MORE_PHOTO']['FILE_VALUE'] ?? [];
    if (!is_array($previewImages)) {
        $previewImages = [];
    }
    if (!empty($previewImages) && !$previewImages[0]) {
        $previewImages = [$previewImages];
    }
    $previewImages = array_map(static fn($row) => $row['SRC'], $previewImages);
    $item['PREVIEW_IMAGES'] = array_splice($previewImages, 0, 1);
    if (!empty($item['PREVIEW_PICTURE']['SRC'])) {
        array_unshift($item['PREVIEW_IMAGES'], $item['PREVIEW_PICTURE']['SRC']);
    }
    $item['NAME'] = Base::getMultiLang(
        $item['NAME'],
        $item['DISPLAY_PROPERTIES']['NAME_EN']['DISPLAY_VALUE'] ?? ''
    );
}
unset($item);
