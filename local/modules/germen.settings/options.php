<?
use \Bitrix\Main\Localization\Loc;
use \UniPlug\Settings;

$module_id = "germen.settings";

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/options.php");
Loc::loadMessages(__FILE__);

if ( !\Bitrix\Main\Loader::includeModule($module_id) ) {
	return;
}

global $USER_FIELD_MANAGER, $APPLICATION;

$MODULE_RIGHTS = $APPLICATION->GetGroupRight($module_id);

if ( $MODULE_RIGHTS < "W" ) {
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
	die();
}

// Hack for RIGHTS saving
if ( strlen($_POST["save"] . $_POST["apply"]) > 0 ) {
	$Update = 'Update';
}

ob_start();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
$MODULE_RIGHTS_CONTENT = ob_get_clean();

if ( $_SERVER['REQUEST_METHOD'] == 'POST' && strlen($_POST["save"] . $_POST["apply"] . $_POST["RestoreDefaults"]) > 0 && check_bitrix_sessid() ) {
	if ( strlen($_POST["RestoreDefaults"]) > 0 ) {
		\UniPlug\Settings::restoreDefault();
	} else {
		Settings::setOptions($_POST["SETTINGS"]);
	}
	LocalRedirect($APPLICATION->GetCurPageParam(), false);
}

$arCustomPage = Settings::getOptions();

$aTabs = array(
	array(
		"DIV"   => "settings",
		"TAB"   => Loc::getMessage("UNIPLUG_SETTINGS_OPTIONS_TAB_SETTINGS"),
		"TITLE" => Loc::getMessage("UNIPLUG_SETTINGS_OPTIONS_TAB_TITLE_SETTINGS"),
	),
	array(
		"DIV"   => "menu",
		"TAB"   => Loc::getMessage("UNIPLUG_SETTINGS_OPTIONS_TAB_MENU"),
		"TITLE" => Loc::getMessage("UNIPLUG_SETTINGS_OPTIONS_TAB_TITLE_MENU"),
	),

	array(
		"DIV"   => "edit_rights",
		"TAB"   => Loc::getMessage("MAIN_TAB_RIGHTS"),
		"TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS"),
	),
);

$rsLang = \CLanguage::GetList($by = "sort", $order = "asc", array("ACTIVE" => "Y"));

$arLang = array();
while ($arLng = $rsLang->Fetch()) {
	$arLang[$arLng["LID"]] = $arLng;
}

$tabControl = new CAdminForm("tabControl", $aTabs);
$tabControl->SetShowSettings(true);
$tabControl->BeginEpilogContent();
	echo bitrix_sessid_post();
$tabControl->EndEpilogContent();

$tabControl->Begin();
$tabControl->BeginNextFormTab();

$tabControl->BeginCustomField("ADD_URL", "FG", true);
?>
<tr>
	<td colspan="2" align="left">
		<a href="/bitrix/admin/userfield_edit.php?lang=<?= LANGUAGE_ID?>&amp;ENTITY_ID=GERMEN_SETTINGS&amp;back_url=<?= urlencode($APPLICATION->GetCurPageParam("tabControl_active_tab=user_fields_tab", array("tabControl_active_tab")))?>"><?=Loc::getMessage("UNIPLUG_SETTINGS_ADD_UF")?></a>
	</td>
</tr>
<?
$tabControl->EndCustomField("ADD_URL");
$arUserFields = $USER_FIELD_MANAGER->GetUserFields("GERMEN_SETTINGS", 0, LANGUAGE_ID);
foreach($arUserFields as $FIELD_NAME => $arUserField) {
	$arUserField['VALUE_ID'] = 1;
	$tabControl->BeginCustomField($arUserField["FIELD_NAME"], empty($arUserField["EDIT_FORM_LABEL"]) ? preg_replace("/^UF_/", "", $arUserField["FIELD_NAME"]) : $arUserField["EDIT_FORM_LABEL"], true);
	?>
	<tr>
		<td align="left">
			<?= preg_replace("/^UF_/", "", $arUserField["FIELD_NAME"])?>:
		</td>
		<td>
			<?= $arUserField['USER_TYPE']['DESCRIPTION'] ?> <?= $arUserField["EDIT_FORM_LABEL"] ? '['.$arUserField["EDIT_FORM_LABEL"].']' : ''?>
			<a href="/bitrix/admin/userfield_edit.php?lang=<?= LANGUAGE_ID?>&amp;ID=<?= $arUserField["ID"]?>&amp;back_url=<?= urlencode($APPLICATION->GetCurPageParam("tabControl_active_tab=user_fields_tab", array("tabControl_active_tab"))) ?>">
				Изменить
			</a>
		</td>
	</tr>
	<?
	$tabControl->EndCustomField($arUserField["FIELD_NAME"]);
}

$tabControl->BeginNextFormTab();
$tabControl->BeginCustomField("TABS", Loc::getMessage("UNIPLUG_SETTINGS_OPTIONS_MENU_PARENT_MENU"), true);
?>
	<tr>
		<td valign="top" width="50%">
			<?= Loc::getMessage("UNIPLUG_SETTINGS_OPTIONS_MENU_PARENT_MENU") ?>
		</td>
		<td valign="top" width="50%">
			<select name="SETTINGS[PARENT_MENU]">
				<option value="global_menu_content" <?= $arCustomPage["PARENT_MENU"] == "global_menu_content" ? 'selected="selected"' : ""?>>
					<?= Loc::getMessage("UNIPLUG_SETTINGS_OPTIONS_MENU_PARENT_MENU_content") ?>
				</option>
				<option value="global_menu_services"<?= $arCustomPage["PARENT_MENU"] == "global_menu_services" ? 'selected="selected"' : ""?>>
					<?= Loc::getMessage("UNIPLUG_SETTINGS_OPTIONS_MENU_PARENT_MENU_services") ?>
				</option>
				<option value="global_menu_store"<?= $arCustomPage["PARENT_MENU"] == "global_menu_store" ? 'selected="selected"' : ""?>>
					<?= Loc::getMessage("UNIPLUG_SETTINGS_OPTIONS_MENU_PARENT_MENU_store") ?>
				</option>
				<option value="global_menu_statistics"<?= $arCustomPage["PARENT_MENU"] == "global_menu_statistics" ? 'selected="selected"' : ""?>>
					<?= Loc::getMessage("UNIPLUG_SETTINGS_OPTIONS_MENU_PARENT_MENU_statistics") ?>
				</option>
				<option value="global_menu_settings"<?= $arCustomPage["PARENT_MENU"] == "global_menu_settings" ? 'selected="selected"' : ""?>>
					<?= Loc::getMessage("UNIPLUG_SETTINGS_OPTIONS_MENU_PARENT_MENU_settings") ?>
				</option>
			</select>
		</td>
	</tr>
	<tr>
		<td valign="top" width="50%">
			<?= Loc::getMessage("UNIPLUG_SETTINGS_OPTIONS_MENU_SORT") ?>
		</td>
		<td valign="top" width="50%">
			<input type="text" name="SETTINGS[SORT]" size="4" value="<?= htmlspecialcharsbx($arCustomPage["SORT"]) ?>"/>
		</td>
	</tr>
	<tr>
		<td valign="top" width="50%">
			<?= Loc::getMessage("UNIPLUG_SETTINGS_OPTIONS_MENU_PARENT_MENU_TEXT") ?>
		</td>
		<td valign="top" width="50%">
			<table cellspacing="0" cellpadding="0" class="uniplug_settings-module-options">
				<?foreach ($arLang as $lang_id => $lang): ?>
					<tr>
						<td><?= $lang_id ?>&nbsp;:</td>
						<td>
							<input type="text" name="SETTINGS[LANG][<?= $lang_id ?>][MENU_TEXT]" size="30" value="<?= htmlspecialcharsbx($arCustomPage["LANG"][$lang_id]["MENU_TEXT"]) ?>"/>
						</td>
					</tr>
				<? endforeach; ?>
			</table>
		</td>
	</tr>
	<tr>
		<td valign="top" width="50%">
			<?= Loc::getMessage("UNIPLUG_SETTINGS_OPTIONS_MENU_PARENT_MENU_TITLE") ?>
		</td>
		<td valign="top" width="50%">
			<table cellspacing="0" cellpadding="0" class="uniplug_settings-module-options">
				<?foreach ($arLang as $lang_id => $lang): ?>
					<tr>
						<td><?= $lang_id ?>&nbsp;:</td>
						<td><input type="text" name="SETTINGS[LANG][<?= $lang_id ?>][MENU_TITLE]" size="30" value="<?= htmlspecialcharsbx($arCustomPage["LANG"][$lang_id]["MENU_TITLE"]) ?>"/></td>
					</tr>
				<? endforeach; ?>
			</table>
		</td>
	</tr>
	<tr>
		<td valign="top" width="50%">
			<?= Loc::getMessage("UNIPLUG_SETTINGS_OPTIONS_MENU_PARENT_PAGE_TITLE") ?>
		</td>
		<td valign="top" width="50%">
			<table cellspacing="0" cellpadding="0" class="uniplug_settings-module-options">
				<?foreach ($arLang as $lang_id => $lang): ?>
					<tr>
						<td><?= $lang_id ?>&nbsp;:</td>
						<td><input type="text" name="SETTINGS[LANG][<?= $lang_id ?>][PAGE_TITLE]" size="30" value="<?= htmlspecialcharsbx($arCustomPage["LANG"][$lang_id]["PAGE_TITLE"]) ?>"/></td>
					</tr>
				<? endforeach; ?>
			</table>
		</td>
	</tr>
<?
$tabControl->EndCustomField("TABS");
$tabControl->BeginNextFormTab();
$tabControl->BeginCustomField("RIGHTS", Loc::getMessage("MAIN_TAB_TITLE_RIGHTS"), true);
echo $MODULE_RIGHTS_CONTENT;
$tabControl->EndCustomField("RIGHTS");

ob_start();
?>
<input type="submit"
name="RestoreDefaults"
OnClick="if (confirm('<?= AddSlashes(Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')) {window.location = '<?=$APPLICATION->GetCurPageParam("RestoreDefaults=Y", array("RestoreDefaults"))?>';} else return false;"
title="<?=Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS")?>"
value="<?=Loc::getMessage('MAIN_RESTORE_DEFAULTS')?>"
/>
<?
$restore_defaults = ob_get_clean();
$tabControl->Buttons(
	array(
		'disabled' => false,
		"back_url" => $APPLICATION->GetCurPageParam('mid='.urlencode($mid) .'&lang='.LANG, array("mid", "lang")),
	), $restore_defaults
);

$tabControl->arParams["FORM_ACTION"] = $APPLICATION->GetCurPageParam('mid='.urlencode($mid) .'&lang='.LANG, array("mid", "lang"));


$tabControl->Show();
?>
