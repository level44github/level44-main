<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @global CMain $APPLICATION
 */

global $APPLICATION;

//delayed function must return a string
if (empty($arResult) || !is_array($arResult))
    return "";

$arResult = array_values(
    array_filter(
        $arResult,
        fn($item, $index) => $item['LINK'] !== SITE_DIR . 'catalog/' || $index === count($arResult) - 1,
        ARRAY_FILTER_USE_BOTH
    )
);

$desktopStrReturn = '<div class="product__nav"><nav class="breadcrumbs">';

$itemSize = count($arResult);
for ($index = 0; $index < $itemSize; $index++) {
    $title = htmlspecialcharsex($arResult[$index]["TITLE"]);

    if ($arResult[$index]["LINK"] <> "" && $index != $itemSize - 1) {
        $desktopStrReturn .= '<a href="' . $arResult[$index]["LINK"] . '">' . $title . '</a><span> / </span>';
    } else {
        $desktopStrReturn .= '<span class="active">' . $title . '</span>';
    }
}

$desktopStrReturn .= '</nav></div>';

return $desktopStrReturn; ?>

