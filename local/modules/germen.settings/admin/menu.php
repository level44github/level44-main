<?
use \Bitrix\Main\Localization\Loc;

$module_id = "germen.settings";

if ( $APPLICATION->GetGroupRight($module_id) != "D" && \Bitrix\Main\Loader::includeModule($module_id) ) {

	Loc::loadMessages(__FILE__);

	$arCustomPage = \UniPlug\Settings::getOptions();

	$aMenu = array(

		array(
			"parent_menu" => $arCustomPage["PARENT_MENU"] ? $arCustomPage["PARENT_MENU"] : "global_menu_settings",
			"sort"        => $arCustomPage["SORT"] ? $arCustomPage["SORT"] : 100,
			"text"        => $arCustomPage["LANG"][LANGUAGE_ID]["MENU_TEXT"] ? $arCustomPage["LANG"][LANGUAGE_ID]["MENU_TEXT"] : Loc::getMessage("UNIPLUG_SETTINGS_MENU_TEXT"),
			"url"         => "germen_settings.php?lang=" . LANGUAGE_ID,
			"title"       => $arCustomPage["LANG"][LANGUAGE_ID]["MENU_TITLE"] ? $arCustomPage["LANG"][LANGUAGE_ID]["MENU_TITLE"] : Loc::getMessage("UNIPLUG_SETTINGS_MENU_TITLE"),
			"icon"        => "germen_settings_menu_icon",
		),
	);

	return $aMenu;
}
return false;
