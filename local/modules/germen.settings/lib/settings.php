<?

namespace UniPlug;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class Settings {
	const MODULE_ID = 'germen.settings';
	public static $LAST_ERROR = "";
	static private $arFields = [];

	public static function get($key = '') {
		self::checkCache();

		$key = (string) $key;

		if ( 0 === strlen($key) ) {
			return self::$arFields;
		}

		return self::$arFields[$key];
	}

	private static function checkCache() {
		if ( 0 === count(self::$arFields) ) {
			$arResult = [];

			$obCache = new \CPHPCache;
			if ( $obCache->InitCache(14400, 0, self::MODULE_ID) ) {
				$arResult = $obCache->GetVars();
			} elseif ( $obCache->StartDataCache() ) {
				$arResult = self::getUF();
				$obCache->EndDataCache($arResult);
			}
			self::$arFields = $arResult;
		}
	}

	private static function getUF() {
		global $USER_FIELD_MANAGER;

		$arResult = [];

		$ID = 1;
		$entity_id = "GERMEN_SETTINGS";

		$arUserFields = $USER_FIELD_MANAGER->GetUserFields($entity_id, $ID, LANGUAGE_ID);

		foreach ($arUserFields as $FIELD_NAME => $arUserField) {
			$arResult[preg_replace("/^UF_/", "", $FIELD_NAME, 1)] = $arUserField['VALUE'];
		}

		return $arResult;
	}

	public static function getOptions() {
		$settings = Option::get(self::MODULE_ID, "menu", false);
		if ( empty($settings) or !($settings = unserialize($settings)) ) {
			self::restoreDefault();

			return self::getDefaultSettings();
		}

		return $settings;
	}

	public static function restoreDefault() {
		/** @var $APPLICATION \CMain */
		global $APPLICATION;

		self::clearCache();
		self::setOptions(self::getDefaultSettings());

		$z = \CGroup::GetList($v1 = "id", $v2 = "asc", ["ACTIVE" => "Y", "ADMIN" => "N"]);
		while ($zr = $z->Fetch()) {
			$APPLICATION->DelGroupRight(self::MODULE_ID, [$zr["ID"]]);
		}
	}

	public static function clearCache() {
		$obCache = new \CPHPCache();
		$obCache->CleanDir(self::MODULE_ID);
		self::$arFields = false;
	}

	public static function setOptions(array $arOptions) {
		Option::set(self::MODULE_ID, "menu", serialize($arOptions));
	}

	private static function getDefaultSettings() {
		return [
			"PARENT_MENU" => "global_menu_services",
			"SORT"        => "100",
			"LANG"        => [
				"ru" => [
					"MENU_TEXT"  => Loc::getMessage("UNIPLUG_SETTINGS_DEFAULT_MENU_RU"),
					"MENU_TITLE" => Loc::getMessage("UNIPLUG_SETTINGS_DEFAULT_MENU_RU"),
					"PAGE_TITLE" => Loc::getMessage("UNIPLUG_SETTINGS_DEFAULT_MENU_RU"),
				],
				"en" => [
					"MENU_TEXT"  => Loc::getMessage("UNIPLUG_SETTINGS_DEFAULT_MENU_EN"),
					"MENU_TITLE" => Loc::getMessage("UNIPLUG_SETTINGS_DEFAULT_MENU_EN"),
					"PAGE_TITLE" => Loc::getMessage("UNIPLUG_SETTINGS_DEFAULT_MENU_EN"),
				],
			],
		];
	}


	public static function update($arFields) {
		$result = true;
		global $APPLICATION;

		self::$LAST_ERROR = "";

		$ID = 1;
		$entity_id = "GERMEN_SETTINGS";

		$APPLICATION->ResetException();
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$arrList = $eventManager->findEventHandlers(self::MODULE_ID, "OnAfterSettingsUpdate");
		foreach ($arrList as $arEvent) {
			$bEventRes = ExecuteModuleEventEx($arEvent, [&$arFields]);
			if ( $bEventRes === false ) {
				if ( $err = $APPLICATION->GetException() ) {
					self::$LAST_ERROR .= $err->GetString();
				} else {
					$APPLICATION->ThrowException("Unknown error");
					self::$LAST_ERROR .= "Unknown error";
				}

				$result = false;
				break;
			}
		}

		if ( $result ) {
			global $USER_FIELD_MANAGER;

			// TODO: check required fields

			$USER_FIELD_MANAGER->Update($entity_id, $ID, $arFields);
			self::ClearCache();


			$eventManager = \Bitrix\Main\EventManager::getInstance();
			$arrList = $eventManager->findEventHandlers(self::MODULE_ID, "OnAfterSettingsUpdate");
			foreach ($arrList as $arEvent) {
				ExecuteModuleEventEx($arEvent, [$arFields]);
			}
		}

		return $result;
	}

}
