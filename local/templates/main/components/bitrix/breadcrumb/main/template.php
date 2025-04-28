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

$mobileStrReturn = '<div class="nav-mobile">';
$desktopStrReturn = '<div class="nav-desktop"><nav class="breadcrumbs">';

$itemSize = count($arResult);
for ($index = 0; $index < $itemSize; $index++) {
    $title = htmlspecialcharsex($arResult[$index]["TITLE"]);

    if ($arResult[$index]["LINK"] <> "" && $index != $itemSize - 1) {
        if ($index === $itemSize - 2) {
            $mobileStrReturn .= '<a class="btn btn-link nav-mobile__link back" href="' . $arResult[$index]["LINK"] . '">
            <svg class="icon icon-arrow-back nav-mobile__link__icon">
                <use xlink:href="#arrow-back"></use>
            </svg>
            <span>' . $title . '</span>
        </a>';
        }

        $desktopStrReturn .= '<a href="' . $arResult[$index]["LINK"] . '">' . $title . '</a><span> / </span>';
    } else {
        $desktopStrReturn .= '<span class="active">' . $title . '</span>';
    }
}

$mobileStrReturn .= '<button class="btn btn-link nav-mobile__link nav-mobile__link__filters" type="button" aria-label="Toggle filters"
            data-open-bottom-sheet="filters-sheet">
        <svg class="icon icon-filters nav-mobile__link__icon">
            <use xlink:href="#filters"></use>
        </svg>
    </button></div>';
$desktopStrReturn .= '</nav></div>';

return $mobileStrReturn . $desktopStrReturn; ?>



