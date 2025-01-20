<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */

global $APPLICATION;
function setActive(array &$items): void
{
    global $APPLICATION;

    foreach ($items as &$item) {
        $children = $item["PARAMS"]["CHILDREN"];

        if (is_array($children)) {
            $item["SELECTED"] = array_filter($children, fn($child) => $child[1] === $APPLICATION->GetCurDir());
        }
    }
    unset($item);
}

foreach ($arResult as &$arItem) {
    if ($arItem["SELECTED"]) {
        if ($arItem["LINK"] !== $APPLICATION->GetCurDir()) {
            $arItem["SELECTED"] = false;
        }
    }
}
unset($arItem);

if (empty(array_filter($arResult, fn($item) => $item["SELECTED"]))) {
    setActive($arResult);
}
