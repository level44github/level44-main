<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use \Bitrix\Main\Localization\Loc;
use \UniPlug\Settings;

$module_id = "germen.settings";

global $USER_FIELD_MANAGER, $APPLICATION;

if ( !\Bitrix\Main\Loader::includeModule($module_id) ) {
	return;
}

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/options.php");
Loc::loadMessages(__FILE__);


$MODULE_RIGHTS = $APPLICATION->GetGroupRight($module_id);

if ( $MODULE_RIGHTS === "D" ) {
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && strlen($_POST["save"]) > 0 && check_bitrix_sessid() && $MODULE_RIGHTS >= "S" ) {
	Settings::$LAST_ERROR = '';
	if ( Settings::update($_POST) ) {
		LocalRedirect($APPLICATION->GetCurPageParam("TYPE=SAVE_OK", ["TYPE"]));
	} else {
		$errorText = Settings::$LAST_ERROR;
	}
}

if ( $_GET["TYPE"] === "SAVE_OK" ) {
	CAdminMessage::ShowMessage(
		[
			'DETAILS' => Loc::getMessage("UNIPLUG_SETTINGS_ADMIN_DATA_SAVED"),
			'HTML'    => true,
			'TYPE'    => 'OK',
		]
	);
}

if ( isset($errorText) && strlen($errorText) > 0 ) {
	CAdminMessage::ShowMessage(
		[
			"TYPE"    => "ERROR",
			"MESSAGE" => $errorText,
			"DETAILS" => "",
			"HTML"    => true,
		]
	);
}

$rsLang = \CLanguage::GetList($by = "sort", $order = "asc", ["ACTIVE" => "Y"]);

$arLang = [];
while ($arLng = $rsLang->Fetch()) {
	$arLang[$arLng["LID"]] = $arLng;
}

$arCustomPage = Settings::getOptions();

$aTabs = [
	[
		"DIV"   => "settings",
		"TAB"   => $arCustomPage["LANG"][LANGUAGE_ID]["PAGE_TITLE"] ? $arCustomPage["LANG"][LANGUAGE_ID]["PAGE_TITLE"] : Loc::getMessage("UNIPLUG_SETTINGS_PAGE_TITLE"),
		"TITLE" => $arCustomPage["LANG"][LANGUAGE_ID]["PAGE_TITLE"] ? $arCustomPage["LANG"][LANGUAGE_ID]["PAGE_TITLE"] : Loc::getMessage("UNIPLUG_SETTINGS_PAGE_TITLE"),
	],
];

$tabControl = new CAdminForm("UNIPLIG_SETTINGS_SETTINGS_tabControl", $aTabs);

$dbSites = \CSite::GetList(($b = ""), ($o = ""), ["ACTIVE" => "Y"]);
$siteList = [];
$aSubTabs = [];
$i = 0;
while ($site = $dbSites->Fetch()) {
	$site["ID"] = htmlspecialcharsbx($site["ID"]);
	$site["NAME"] = htmlspecialcharsbx($site["NAME"]);
	$siteList[] = $site;
	$aSubTabs[] = ["DIV" => "opt_site_" . $site["ID"], "TAB" => "(" . $site["ID"] . ") " . $site["NAME"], 'TITLE' => ''];
}

unset($dbSites, $site);

if ( $MODULE_RIGHTS < "S" ) {
	$tabControl->SetShowSettings(false);
}

$tabControl->BeginEpilogContent();
echo bitrix_sessid_post();
$tabControl->EndEpilogContent();

$tabControl->Begin();
$tabControl->BeginNextFormTab();

$arUserFields = $USER_FIELD_MANAGER->GetUserFields("GERMEN_SETTINGS", 1, LANGUAGE_ID);
foreach ($arUserFields as $FIELD_NAME => $arUserField) {
	$arUserField['VALUE_ID'] = 1;
	$FIELD_NAME = false;
	$tabControl->BeginCustomField($arUserField["FIELD_NAME"], preg_replace("/^UF_/", "", $arUserField["FIELD_NAME"]));
	echo $USER_FIELD_MANAGER->GetEditFormHTML(false, $FIELD_NAME, $arUserField);
	$tabControl->EndCustomField($arUserField["FIELD_NAME"]);
}
$tabControl->Buttons(
	[
		'btnApply' => false,
		'disabled' => $MODULE_RIGHTS < "S",
		"back_url" => $APPLICATION->GetCurPageParam(),
	]
);

$tabControl->Show();

if ( $MODULE_RIGHTS === "W" ):?>
	<?= BeginNote(); ?>
	<?= Loc::getMessage("UNIPLUG_SETTINGS_OPTIONS_NOTE") ?>
	<?= EndNote(); ?>
<? endif ?>
<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
