<?php

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\EventManager;
use Podeli\Bnpl\CompatibilityChecker;
use Podeli\Bnpl\FileInstaller;

class podeli_bnpl extends CModule
{
    const MODULE_ID = "podeli.bnpl";

    public $MODULE_ID = "podeli.bnpl";
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;

    private $errors;

    public function __construct()
    {
        Loc::loadMessages(__FILE__);
        $this->MODULE_NAME = Loc::getMessage('PODELI.MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('PODELI.MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('PODELI.PARTNER_NAME');
        $this->PARTNER_URI = 'https://podeli.ru/';
        $this->MODULE_GROUP_RIGHTS = 'Y';
        $this->setVersionInfo();
        require_once __DIR__ . '/DatabaseInstaller.php';
        require_once __DIR__ . '/FileInstaller.php';
    }

    public function GetModuleRightList()
    {
        $rightReferenceIds = ['D', 'W'];
        $references = [];
        foreach ($rightReferenceIds as $referenceId) {
            $references[] = "[$referenceId]" . Loc::getMessage('PODELI.MODULE_REFERENCE_RIGHT_' . $referenceId);
        }
        return [
            'reference_id' => $rightReferenceIds,
            'reference' => $references
        ];
    }

    public function GetModuleTasks()
    {
        return [];
    }

    public function DoInstall()
    {
        if (!$this->InstallDB()) {
            return false;
        }
        if (!$this->InstallEvents()) {
            $this->UninstallDB();
            return false;
        }
        if (!$this->InstallFiles()) {
            $this->UninstallDB();
            $this->UninstallFiles();
            return false;
        }
        return true;
    }


    public function DoUninstall()
    {
        if (!$this->UnInstallDB()) {
            return false;
        }
        $this->UnInstallEvents();
        $this->UnInstallFiles();
        return true;
    }

    public function InstallDB()
    {
        if (!$this->checkCompatibility()) {
            return false;
        }
        $connection = \Bitrix\Main\Application::getConnection();
        $installer = new \Podeli\Bnpl\DatabaseInstaller(self::MODULE_ID, $connection);
        try {
            return $installer->install();
        } catch (Exception $ex) {
            global $APPLICATION;
            $APPLICATION->ResetException();
            $APPLICATION->ThrowException(Loc::getMessage('PODELI.DB_INSTALLATION_ERROR', [
                'ERROR' => $ex->getMessage()
            ]));
            return false;
        }
    }

    public function UnInstallDB()
    {
        $connection = \Bitrix\Main\Application::getConnection();
        $installer = new \Podeli\Bnpl\DatabaseInstaller(self::MODULE_ID, $connection);
        try {
            return $installer->uninstall();
        } catch (Exception $ex) {
            global $APPLICATION;
            $APPLICATION->ResetException();
            $APPLICATION->ThrowException(Loc::getMessage('PODELI.DB_UNINSTALLATION_ERROR', [
                'ERROR' => $ex->getMessage()
            ]));
            return false;
        }
    }

    public function InstallEvents()
    {
        RegisterModuleDependences('main', 'OnBeforeEndBufferContent', self::MODULE_ID);
        return true;
    }

    public function UnInstallEvents()
    {
        UnRegisterModuleDependences('main', 'OnBeforeEndBufferContent', self::MODULE_ID);
        return true;
    }

    public function InstallFiles()
    {
        global $APPLICATION;
        if (!$this->checkCompatibility()) {
            return false;
        }
        try {
            $installer = new FileInstaller(__DIR__ . '/../', Application::getDocumentRoot(), $APPLICATION->GetTemplatePath(''));
            $installer->install();
        } catch (\Bitrix\Main\SystemException $e) {
            $APPLICATION->ResetException();
            $APPLICATION->ThrowException(Loc::getMessage('PODELI.FILES_INSTALLATION_ERROR', [
                'ERROR' => $e->getMessage()
            ]));
            return false;
        }

        return true;
    }

    public function UnInstallFiles()
    {
        global $APPLICATION;
        try {
            $installer = new FileInstaller(__DIR__ . '/../', Application::getDocumentRoot(), $APPLICATION->GetTemplatePath(''));
            $installer->uninstall();
        } catch (\Bitrix\Main\SystemException $e) {
            $APPLICATION->ResetException();
            $APPLICATION->ThrowException(Loc::getMessage('PODELI.FILES_UNINSTALLATION_ERROR', [
                'ERROR' => $e->getMessage()
            ]));
            return false;
        }
        return true;
    }

    protected function setVersionInfo()
    {
        $versionInfo = $this->getModuleVersionInfo();
        $this->MODULE_VERSION = $versionInfo['VERSION'];
        $this->MODULE_VERSION_DATE = $versionInfo['VERSION_DATE'];
    }

    protected function getModuleVersionInfo()
    {
        $arModuleVersion = [];
        include __DIR__ . '/version.php';
        $defaultVersionInfo = $this->getDefaultVersionInfo();
        if (!is_array($arModuleVersion) || empty($arModuleVersion)) {
            return $defaultVersionInfo;
        }
        $version = isset($arModuleVersion['VERSION']) ? $arModuleVersion['VERSION']
            : $defaultVersionInfo['VERSION'];
        $versionDate = isset($arModuleVersion['VERSION_DATE']) ? $arModuleVersion['VERSION_DATE']
            : $defaultVersionInfo['VERSION_DATE'];
        return [
            'VERSION' => $version,
            'VERSION_DATE' => $versionDate
        ];
    }

    protected function getDefaultVersionInfo()
    {
        return [
            'VERSION' => '1.0.0',
            'VERSION_DATE' => '1970-01-01 00:00:00'
        ];
    }

    protected function checkCompatibility()
    {
        include_once __DIR__ . '/../lib/CompatibilityChecker.php';
        $checker = new CompatibilityChecker();
        $result = $checker->check();
        if (!$result->isSuccess()) {
            global $APPLICATION;
            $APPLICATION->ThrowException(implode('. ', $result->getErrorMessages()));
            return false;
        }
        return true;
    }
}
