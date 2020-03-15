<?

IncludeModuleLangFile(__FILE__);

class germen_settings extends CModule {
	const MODULE_ID = 'germen.settings';
	var $MODULE_ID = 'germen.settings';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $PARTNER_NAME;
	var $PARTNER_URI;
	var $MODULE_GROUP_RIGHTS = "Y";
	public $NEED_MODULES = ["main" => "14.0.0"];

	/**
	 * Инициализация модуля для страницы 'Управление модулями'
	 */
	public function __construct() {
		include(__DIR__ . '/version.php');
		/** @var array $arModuleVersion */
		$this->MODULE_NAME = GetMessage('UNIPLUG_SETTINGS_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('UNIPLUG_SETTINGS_MODULE_DESC');
		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		$this->PARTNER_NAME = "Germen";
		$this->PARTNER_URI = "https://germen.me";
	}

	/**
	 * Устанавливаем модуль
	 */
	public function DoInstall() {
		if ( !$this->InstallFiles() ) {
			return false;
		}
		RegisterModule(self::MODULE_ID);

		CModule::IncludeModule(self::MODULE_ID);

		return true;
	}

	/**
	 * Копируем файлы административной части
	 *
	 * @return bool
	 */
	public function InstallFiles() {

		CopyDirFiles(__DIR__ . '/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin', true, true);
		CopyDirFiles(__DIR__ . '/themes', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes', true, true);

		return true;
	}

	/**
	 * Удаляем модуль
	 */
	public function DoUninstall() {
		if ( !$this->checkVersion() ) {
			return false;
		}

		$this->UnInstallFiles();

		UnRegisterModule(self::MODULE_ID);

		COption::RemoveOption(self::MODULE_ID);

		return true;
	}

	private function checkVersion() {
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		if ( is_array($this->NEED_MODULES) && !empty($this->NEED_MODULES) ) {
			foreach ($this->NEED_MODULES as $module => $version) {
				$module = strtolower($module);
				if ( !IsModuleInstalled($module) ) {
					$APPLICATION->ThrowException(GetMessage('UNIPLUG_MODULES_NEED_INSTALL', ['#MODULE#' => $module, '#VERSION#' => $version]));

					return false;
				} else {
					$info = \CModule::CreateModuleObject($module);
					if ( !$info || !CheckVersion($info->MODULE_VERSION, $version) ) {
						$APPLICATION->ThrowException(GetMessage('UNIPLUG_MODULES_NEED_UPDATE', ['#MODULE#' => $module, '#VERSION#' => $version]));

						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Удаляем файлы административной части
	 *
	 * @return bool
	 */
	function UnInstallFiles() {
		DeleteDirFiles(__DIR__ . "/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");
		DeleteDirFiles(__DIR__ . "/themes", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes");

		return true;
	}

	function GetModuleRightList() {
		$arr = [
			"reference_id" => ["D", "R", "S", "W"],
			"reference"    => [
				"[D] " . GetMessage("UNIPLUG_SETTINGS_RIGHTS_D"),
				"[R] " . GetMessage("UNIPLUG_SETTINGS_RIGHTS_R"),
				"[S] " . GetMessage("UNIPLUG_SETTINGS_RIGHTS_S"),
				"[W] " . GetMessage("UNIPLUG_SETTINGS_RIGHTS_W"),
			],
		];

		return $arr;
	}

}
