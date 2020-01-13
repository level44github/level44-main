<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

global $APPLICATION;

foreach ($arResult as &$arItem) {
	if ($arItem["SELECTED"]) {
		if ($arItem["LINK"] !== $APPLICATION->GetCurDir()) {
//			$arItem["SELECTED"] = false;
		}
	}
}
unset($arItem);
