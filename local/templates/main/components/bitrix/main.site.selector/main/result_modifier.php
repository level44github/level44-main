<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!function_exists('str_replace_once')) {
    function str_replace_once($search, $replace, $text)
    {
        $pos = strpos($text, $search);

        return $pos !== false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
    }
}

$arResult["CURRENT"] = current(
    array_filter(
        $arResult["SITES"],
        function ($site) {
            return $site["CURRENT"] === "Y";
        }
    )
);

$arResult["CURRENT_DIR"] = "";

if (stripos($APPLICATION->GetCurPageParam(), $arResult["CURRENT"]["DIR"]) === 0) {
    $arResult["CURRENT_DIR"] = str_replace_once($arResult["CURRENT"]["DIR"], "", $APPLICATION->GetCurPageParam());
}

$arResult["SITES"] = array_filter(
    $arResult["SITES"],
    function ($site) {
        return $site["CURRENT"] !== "Y";
    }
);