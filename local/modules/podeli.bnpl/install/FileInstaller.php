<?php

namespace Podeli\Bnpl;

use Bitrix\Main\IO\FileNotOpenedException;
use Bitrix\Main\Localization\Loc;
use http\Exception\InvalidArgumentException;
use Bitrix\Main\IO\IoException;

class FileInstaller {


    protected $documentRoot;
    protected $moduleDir;
    protected $moduleId;
    protected $modulePaymentId;
    protected $moduleAdminDir;
    protected $moduleAdminJSDir;
    protected $moduleJSDir;
    protected $moduleLangDir;
    protected $modulePaymentDir;
    protected $moduleToolsDir;
    protected $modulePaymentTemplateDir;
    protected $modulePaymentImageDir;
    protected $moduleImagesDir;
    protected $moduleFontsDir;
    protected $moduleThemesDefaultDir;
    protected $moduleCSSDir;

    protected $bitrixModulesDir;
    protected $bitrixAdminLinksDir;
    protected $bitrixJSDir;
    protected $bitrixToolsDir;
    protected $bitrixPHPInterfaceIncludeDir;
    protected $bitrixPaymentTemplatesDir;
    protected $bitrixPaymentImagesDir;
    protected $bitrixImagesDir;
    protected $bitrixFontsDir;
    protected $bitrixThemesDefaultDir;
    protected $bitrixCSSDir;

    public function __construct($moduleDir, $documentRoot, $templatePath) {
        Loc::loadMessages(__FILE__);
        $this->documentRoot = realpath($documentRoot);
        if (!$this->documentRoot) {
            throw new InvalidArgumentException(Loc::getMessage('PODELI.FILE_INSTALLER_DOCROOT_INVALID'));
        }
        $moduleDir = realpath($moduleDir);
        if (!$moduleDir) {
            throw new InvalidArgumentException(Loc::getMessage('PODELI.FILE_INSTALLER_MODULE_DIR_INVALID'));
        }
        $this->moduleDir = $moduleDir;
        $this->moduleId = basename($moduleDir);
        $this->modulePaymentId = explode('.', $this->moduleId)[0];

        $this->moduleAdminDir = $this->moduleDir . self::createPath('admin');
        $this->moduleAdminJSDir = $this->moduleDir . self::createPath('install', 'admin', 'js');
        $this->moduleJSDir = $this->moduleDir . self::createPath('install', 'js');
        $this->moduleLangDir = $this->moduleDir . self::createPath('lang');
        $this->modulePaymentDir = $this->moduleDir . self::createPath('payment', $this->modulePaymentId);
        $this->moduleToolsDir = $this->moduleDir . self::createPath('tools');
        $this->modulePaymentTemplateDir = $this->moduleDir . self::createPath('payment', $this->modulePaymentId, 'template');
        $this->modulePaymentImageDir = $this->moduleDir . self::createPath('install', 'logo');
        $this->moduleImagesDir = $this->moduleDir . self::createPath('install', 'images');
        $this->moduleFontsDir = $this->moduleDir . self::createPath('install', 'fonts');
        $this->moduleThemesDefaultDir = $this->moduleDir . self::createPath('install', 'themes', '.default');
        $this->moduleCSSDir = $this->moduleDir . self::createPath('install', 'css');

        $this->bitrixAdminLinksDir = $this->documentRoot . self::createPath('bitrix', 'admin');
        $this->bitrixModulesDir = $this->documentRoot . self::createPath('bitrix', 'modules');
        $this->bitrixJSDir = $this->documentRoot . self::createPath('bitrix', 'js');
        $this->bitrixToolsDir = $this->documentRoot . self::createPath('bitrix', 'tools');
        $this->bitrixPHPInterfaceIncludeDir = $this->documentRoot . self::createPath('bitrix', 'php_interface', 'include');
        $this->bitrixPaymentTemplatesDir = $this->documentRoot . $templatePath;
        $this->bitrixPaymentImagesDir = $this->documentRoot . self::createPath('bitrix', 'images', 'sale', 'sale_payments');
        $this->bitrixImagesDir = $this->documentRoot . self::createPath('bitrix', 'images');
        $this->bitrixFontsDir = $this->documentRoot . self::createPath('bitrix', 'fonts');
        $this->bitrixThemesDefaultDir = $this->documentRoot . self::createPath('bitrix', 'themes', '.default');
        $this->bitrixCSSDir = $this->documentRoot . self::createPath('bitrix', 'css');
    }

    private static function createPath() {
        $s = DIRECTORY_SEPARATOR;
        $parts = func_get_args();
        return str_replace([$s.$s, '//',], $s, $s . implode($s, $parts));
    }

    protected function checkWriteability() {
        if (!is_writable($this->bitrixAdminLinksDir)) {
            throw new IoException('PODELI.FILE_INSTALLER_NOT_ENOUGH_RIGHTS', [
                'DIRECTORY' => $this->bitrixAdminLinksDir
            ]);
        }
        if (!is_writable($this->bitrixModulesDir)) {
            throw new IoException('PODELI.FILE_INSTALLER_NOT_ENOUGH_RIGHTS', [
                'DIRECTORY' => $this->bitrixModulesDir
            ]);
        }
        if (!is_writable($this->bitrixJSDir)) {
            throw new IoException('PODELI.FILE_INSTALLER_NOT_ENOUGH_RIGHTS', [
                'DIRECTORY' => $this->bitrixJSDir
            ]);
        }
        if (!is_writable($this->bitrixToolsDir)) {
            throw new IoException('PODELI.FILE_INSTALLER_NOT_ENOUGH_RIGHTS', [
                'DIRECTORY' => $this->bitrixToolsDir
            ]);
        }
        if (!is_writable($this->bitrixPHPInterfaceIncludeDir)) {
            throw new IoException('PODELI.FILE_INSTALLER_NOT_ENOUGH_RIGHTS', [
                'DIRECTORY' => $this->bitrixPHPInterfaceIncludeDir
            ]);
        }
        if (!is_writable($this->bitrixPaymentTemplatesDir)) {
            throw new IoException('PODELI.FILE_INSTALLER_NOT_ENOUGH_RIGHTS', [
                'DIRECTORY' => $this->bitrixPaymentTemplatesDir
            ]);
        }
        if (!is_writable($this->bitrixPaymentImagesDir)) {
            throw new IoException('PODELI.FILE_INSTALLER_NOT_ENOUGH_RIGHTS', [
                'DIRECTORY' => $this->bitrixPaymentImagesDir
            ]);
        }
        if (!is_writable($this->bitrixThemesDefaultDir)) {
            throw new IoException('PODELI.FILE_INSTALLER_NOT_ENOUGH_RIGHTS', [
                'DIRECTORY' => $this->bitrixThemesDefaultDir
            ]);
        }
        if (!is_writable($this->bitrixCSSDir)) {
            throw new IoException('PODELI.FILE_INSTALLER_NOT_ENOUGH_RIGHTS', [
                'DIRECTORY' => $this->bitrixCSSDir
            ]);
        }
        if (!is_writable($this->bitrixImagesDir)) {
            throw new IoException('PODELI.FILE_INSTALLER_NOT_ENOUGH_RIGHTS', [
                'DIRECTORY' => $this->bitrixImagesDir
            ]);
        }
        if (!is_writable($this->bitrixFontsDir)) {
            throw new IoException('PODELI.FILE_INSTALLER_NOT_ENOUGH_RIGHTS', [
                'DIRECTORY' => $this->bitrixFontsDir
            ]);
        }
    }

    public function install() {
        $this->checkWriteability();
        $this->installAdminScripts();
        $this->installJSScripts();
        $this->installAdminJSScripts();
        $this->installLangScripts();
        $this->installToolsScripts();
        $this->installPaymentScripts();
        $this->installPaymentTemplate();
        $this->installPaymentImage();
        $this->installThemesDefaultCss();
        $this->installCss();
        $this->installImages();
        $this->installFonts();
        return true;
    }

    public function uninstall() {
        $this->checkWriteability();
        $this->uninstallAdminScripts();
        $this->uninstallJSScripts();
        $this->uninstallBitrixModuleScripts();
        $this->uninstallToolsScripts();
        $this->uninstallPaymentScripts();
        $this->uninstallPaymentTemplates();
        $this->uninstallPaymentImage();
        $this->uninstallThemesDefaultCss();
        $this->uninstallCss();
        $this->uninstallImages();
        $this->uninstallFonts();
        return true;
    }

    protected function installLangScripts() {
        $bitrixModuleDir = self::createPath($this->bitrixModulesDir, $this->moduleId);
        if (!is_dir($bitrixModuleDir)) {
            if (!mkdir($bitrixModuleDir, 0755)) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_DIRECTORY', [
                    'PATH' => $bitrixModuleDir
                ]));
            }
        }
        $bitrixModuleLangDir = self::createPath($bitrixModuleDir, 'lang');
        if (!is_dir($bitrixModuleLangDir)) {
            if (!mkdir($bitrixModuleLangDir, 0755)) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_DIRECTORY', [
                    'PATH' => $bitrixModuleLangDir
                ]));
            }
        }
        $this->recursiveCopyDir($this->moduleLangDir, $bitrixModuleLangDir);
    }

    protected function uninstallBitrixModuleScripts() {
        $bitrixModuleDir = self::createPath($this->bitrixModulesDir, $this->moduleId);
        if (is_dir($bitrixModuleDir)) {
            $this->recursiveRemoveDir($bitrixModuleDir);
        }
    }

    protected function recursiveCopyDir($fromDir, $toDir) {
        try {
            $iterator = new \DirectoryIterator($fromDir);
        }
        catch (\UnexpectedValueException $ex) {
            throw new FileNotOpenedException($this->moduleLangDir, $ex);
        }
        $toDirCopy = $toDir;
        foreach ($iterator as $dirObject) {
            if ($dirObject->isDot()) {
                continue;
            }
            $info = $dirObject->getFileInfo();

            if ($dirObject->isFile()) {
                if ($info->getExtension() == 'php') {
                    if ($info->getRealpath() != self::createPath($toDirCopy, $info->getFilename())) {
                        if (!copy($info->getRealPath(), self::createPath($toDirCopy, $info->getFilename()))) {
                            throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_FILE', [
                                'FILE' => $info->getFilename()
                            ]));
                        }
                    }
                }
            }
            if ($dirObject->isDir()) {
                $toDir = self::createPath($toDirCopy, $info->getBasename());
                if (!is_dir($toDir)) {
                    if (!mkdir($toDir, 0755)) {
                        throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_DIRECTORY', [
                            'PATH' => $toDir
                        ]));
                    }
                }
                $fromDir = $info->getRealPath();
                $this->recursiveCopyDir($fromDir, $toDir);
            }
        }
    }

    protected function recursiveRemoveDir($dir) {
        if (!is_dir($dir)) return;
        $includes = new \FilesystemIterator($dir);
        foreach ($includes as $include) {
            if(is_dir($include) && !is_link($include)) {
                $this->recursiveRemoveDir($include);
            }
            else {
                if (!unlink($include)) {
                    throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_DELETE_FILE', [
                        'FILE' => $include->getFilename()
                    ]));
                }
            }
        }
        if (!rmdir($dir)) {
            throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_DELETE_DIRECTORY', [
                'PATH' => $dir
            ]));
        }
    }

    protected function installJSScripts() {
        try {
            $iterator = new \DirectoryIterator($this->moduleJSDir);
        }
        catch (\UnexpectedValueException $ex) {
            throw new FileNotOpenedException($this->moduleJSDir, $ex);
        }
        foreach ($iterator as $dirObject) {
            if (!$dirObject->isFile()) {
                continue;
            }
            $file = $dirObject->getFileInfo();
            if ($file->getExtension() !== 'js') {
                continue;
            }
            $bitrixModulePath = self::createPath($this->bitrixJSDir, $this->moduleId);
            if (!is_dir($bitrixModulePath)) {
                if (!mkdir($bitrixModulePath, 0755)) {
                    throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_DIRECTORY', [
                        'PATH' => $bitrixModulePath
                    ]));
                }
            }
            if (!copy($file->getRealPath(), self::createPath($bitrixModulePath, $file->getFilename()))) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_FILE', [
                    'FILE' => $file->getFilename()
                ]));
            }
        }
    }

    protected function installAdminJSScripts() {
        try {
            $iterator = new \DirectoryIterator($this->moduleAdminJSDir);
        }
        catch (\UnexpectedValueException $ex) {
            throw new FileNotOpenedException($this->moduleAdminJSDir, $ex);
        }
        foreach ($iterator as $dirObject) {
            if (!$dirObject->isFile()) {
                continue;
            }
            $file = $dirObject->getFileInfo();
            if ($file->getExtension() !== 'js') {
                continue;
            }
            $bitrixModulePath = self::createPath($this->bitrixJSDir, $this->moduleId);
            if (!is_dir($bitrixModulePath)) {
                if (!mkdir($bitrixModulePath, 0755)) {
                    throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_DIRECTORY', [
                        'PATH' => $bitrixModulePath
                    ]));
                }
            }
            $bitrixModuleAdminPath = self::createPath($bitrixModulePath, 'admin');
            if (!is_dir($bitrixModuleAdminPath)) {
                if (!mkdir($bitrixModuleAdminPath, 0755)) {
                    throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_DIRECTORY', [
                        'PATH' => $bitrixModuleAdminPath
                    ]));
                }
            }
            if (!copy($file->getRealPath(), self::createPath($bitrixModuleAdminPath, $file->getFilename()))) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_FILE', [
                    'FILE' => $file->getFilename()
                ]));
            }
        }
    }

    protected function uninstallJSScripts() {
        $pattern = self::createPath($this->bitrixJSDir, $this->moduleId, 'admin', '*.js');
        foreach (glob($pattern) as $file) {
            if (!unlink($file)) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_DELETE_FILE', [
                    'FILE' => $file
                ]));
            }
        }
        $pattern = self::createPath($this->bitrixJSDir, $this->moduleId, '*.js');
        foreach (glob($pattern) as $file) {
            if (!unlink($file)) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_DELETE_FILE', [
                    'FILE' => $file
                ]));
            }
        }
        $this->recursiveRemoveDir(self::createPath($this->bitrixJSDir, $this->moduleId));
    }

    protected function installAdminScripts() {
        $relAdminScriptDir = str_replace($this->documentRoot, '', $this->moduleAdminDir);
        try {
            $iterator = new \DirectoryIterator($this->moduleAdminDir);
        }
        catch (\UnexpectedValueException $ex) {
            throw new FileNotOpenedException($this->moduleAdminDir, $ex);
        }
        foreach ($iterator as $dirObject) {
            if (!$dirObject->isFile()) {
                continue;
            }
            $file = $dirObject->getFileInfo();
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $baseName = $file->getBasename();
            if ($baseName === 'menu.php') {
                continue;
            }
            $linkFileName = $this->moduleId . '_' . $file->getFilename();
            $linkFileDir = $this->documentRoot . self::createPath('bitrix', 'admin');
            $link = self::createPath($relAdminScriptDir, $file->getFilename());
            $linkFileContents = '<?require $_SERVER[\'DOCUMENT_ROOT\']."' . $link . '";?>';
            if (!file_put_contents(self::createPath($linkFileDir, $linkFileName), $linkFileContents)) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_FILE', [
                    'FILE' => $linkFileName
                ]));
            }
        }
    }

    protected function uninstallAdminScripts()
    {
        $pattern = self::createPath($this->bitrixAdminLinksDir, $this->moduleId.'_*');
        foreach (glob($pattern) as $file) {
            if (!unlink($file)) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_DELETE_FILE', [
                    'FILE' => $file
                ]));
            }
        }
    }

    protected function installToolsScripts() {
        try {
            $iterator = new \DirectoryIterator($this->moduleToolsDir);
        }
        catch (\UnexpectedValueException $ex) {
            throw new FileNotOpenedException($this->moduleToolsDir, $ex);
        }
        foreach ($iterator as $dirObject) {
            if (!$dirObject->isFile()) {
                continue;
            }
            $file = $dirObject->getFileInfo();
            if ($file->getExtension() !== 'php') {
                continue;
            }
            if (!copy($file->getRealPath(), self::createPath($this->bitrixToolsDir, $file->getFilename()))) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_FILE', [
                    'FILE' => $file->getFilename()
                ]));
            }
        }
    }

    protected function uninstallToolsScripts() {
        $pattern = self::createPath($this->bitrixToolsDir, 'podeli_*.php');
        foreach (glob($pattern) as $file) {
            if (!unlink($file)) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_DELETE_FILE', [
                    'FILE' => $file
                ]));
            }
        }
    }

    protected function installPaymentScripts() {
        $bitrixSalePaymentDir = self::createPath($this->bitrixPHPInterfaceIncludeDir, 'sale_payment');
        if (!is_dir($bitrixSalePaymentDir)) {
            if (!mkdir($bitrixSalePaymentDir, 0755)) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_DIRECTORY', [
                    'PATH' => $bitrixSalePaymentDir
                ]));
            }
        }
        $bitrixPaymentPodeliDir = self::createPath($bitrixSalePaymentDir, $this->modulePaymentId);
        if (!is_dir($bitrixPaymentPodeliDir)) {
            if (!mkdir($bitrixPaymentPodeliDir, 0755)) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_DIRECTORY', [
                    'PATH' => $bitrixPaymentPodeliDir
                ]));
            }
        }
        $this->recursiveCopyDir($this->modulePaymentDir, $bitrixPaymentPodeliDir);
    }

    protected function uninstallPaymentScripts() {
        $bitrixPodeliPaymentDir = self::createPath($this->bitrixPHPInterfaceIncludeDir, 'sale_payment', $this->modulePaymentId);
        if (is_dir($bitrixPodeliPaymentDir)) {
            $this->recursiveRemoveDir($bitrixPodeliPaymentDir);
        }
    }

    protected function installPaymentTemplate() {
        $bitrixPaymentTemplatesDir = self::createPath($this->bitrixPaymentTemplatesDir, 'payment');
        if (!is_dir($bitrixPaymentTemplatesDir)) {
            if (!mkdir($bitrixPaymentTemplatesDir, 0755)) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_DIRECTORY', [
                    'PATH' => $bitrixPaymentTemplatesDir
                ]));
            }
        }
        $bitrixPaymentTemplateDir = self::createPath($bitrixPaymentTemplatesDir, $this->modulePaymentId);
        if (!is_dir($bitrixPaymentTemplateDir)) {
            if (!mkdir($bitrixPaymentTemplateDir, 0755)) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_DIRECTORY', [
                    'PATH' => $bitrixPaymentTemplateDir
                ]));
            }
        }
        $this->recursiveCopyDir($this->modulePaymentTemplateDir, $bitrixPaymentTemplateDir);
    }

    protected function uninstallPaymentTemplates() {
        $bitrixPaymentTemplateDir = self::createPath($this->bitrixPaymentTemplatesDir, 'payment', $this->modulePaymentId);
        if (is_dir($bitrixPaymentTemplateDir)) {
            $this->recursiveRemoveDir($bitrixPaymentTemplateDir);
        }
    }

    protected function installPaymentImage() {
        try {
            $iterator = new \DirectoryIterator($this->modulePaymentImageDir);
        }
        catch (\UnexpectedValueException $ex) {
            throw new FileNotOpenedException($this->modulePaymentImageDir, $ex);
        }
        foreach ($iterator as $dirObject) {
            if (!$dirObject->isFile()) {
                continue;
            }
            $file = $dirObject->getFileInfo();
            if ($file->getExtension() !== 'png') {
                continue;
            }
            if (!copy($file->getRealPath(), self::createPath($this->bitrixPaymentImagesDir, $file->getFilename()))) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_FILE', [
                    'FILE' => $file->getFilename()
                ]));
            }
        }
    }

    protected function uninstallPaymentImage() {
        $pattern = self::createPath($this->bitrixPaymentImagesDir, $this->modulePaymentId . '.png');
        foreach (glob($pattern) as $file) {
            if (!unlink($file)) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_DELETE_FILE', [
                    'FILE' => $file
                ]));
            }
        }
    }

    protected function installThemesDefaultCss() {
        try {
            $iterator = new \DirectoryIterator($this->moduleThemesDefaultDir);
        }
        catch (\UnexpectedValueException $ex) {
            throw new FileNotOpenedException($this->moduleThemesDefaultDir, $ex);
        }
        foreach ($iterator as $dirObject) {
            if (!$dirObject->isFile()) {
                continue;
            }
            $file = $dirObject->getFileInfo();
            if ($file->getExtension() !== 'css') {
                continue;
            }
            if (!copy($file->getRealPath(), self::createPath($this->bitrixThemesDefaultDir, $file->getFilename()))) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_FILE', [
                    'FILE' => $file->getFilename()
                ]));
            }
        }
    }

    protected function uninstallThemesDefaultCss() {
        $pattern = self::createPath($this->bitrixThemesDefaultDir, 'podeli.bnpl.css');
        foreach (glob($pattern) as $file) {
            if (!unlink($file)) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_DELETE_FILE', [
                    'FILE' => $file
                ]));
            }
        }
        $this->recursiveRemoveDir(self::createPath($this->bitrixThemesDefaultDir, $this->moduleId));
    }

    protected function installCss() {
        try {
            $iterator = new \DirectoryIterator($this->moduleCSSDir);
        }
        catch (\UnexpectedValueException $ex) {
            throw new FileNotOpenedException($this->moduleCSSDir, $ex);
        }
        foreach ($iterator as $dirObject) {
            if (!$dirObject->isFile()) {
                continue;
            }
            $file = $dirObject->getFileInfo();
            if ($file->getExtension() !== 'css') {
                continue;
            }
            $bitrixModulePath = self::createPath($this->bitrixCSSDir, $this->moduleId);
            if (!is_dir($bitrixModulePath)) {
                if (!mkdir($bitrixModulePath, 0755)) {
                    throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_DIRECTORY', [
                        'PATH' => $bitrixModulePath
                    ]));
                }
            }
            if (!copy($file->getRealPath(), self::createPath($bitrixModulePath, $file->getFilename()))) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_FILE', [
                    'FILE' => $file->getFilename()
                ]));
            }
        }
    }
    protected function uninstallCss() {
        $pattern = self::createPath($this->bitrixCSSDir, $this->moduleId, '*.css');
        foreach (glob($pattern) as $file) {
            if (!unlink($file)) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_DELETE_FILE', [
                    'FILE' => $file
                ]));
            }
        }
        $this->recursiveRemoveDir(self::createPath($this->bitrixCSSDir, $this->moduleId));
    }

    protected function installImages() {
        try {
            $iterator = new \DirectoryIterator($this->moduleImagesDir);
        }
        catch (\UnexpectedValueException $ex) {
            throw new FileNotOpenedException($this->moduleImagesDir, $ex);
        }
        foreach ($iterator as $dirObject) {
            if (!$dirObject->isFile()) {
                continue;
            }
            $file = $dirObject->getFileInfo();
            if (!in_array($file->getExtension(), ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
                continue;
            }
            $bitrixModulePath = self::createPath($this->bitrixImagesDir, $this->moduleId);
            if (!is_dir($bitrixModulePath)) {
                if (!mkdir($bitrixModulePath, 0755)) {
                    throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_DIRECTORY', [
                        'PATH' => $bitrixModulePath
                    ]));
                }
            }
            if (!copy($file->getRealPath(), self::createPath($bitrixModulePath, $file->getFilename()))) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_FILE', [
                    'FILE' => $file->getFilename()
                ]));
            }
        }
    }

    protected function uninstallImages() {
        $pattern = self::createPath($this->bitrixImagesDir, $this->moduleId, '*');
        foreach (glob($pattern) as $file) {
            if (!unlink($file)) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_DELETE_FILE', [
                    'FILE' => $file
                ]));
            }
        }
        $this->recursiveRemoveDir(self::createPath($this->bitrixImagesDir, $this->moduleId));
    }

    protected function installFonts() {
        try {
            $iterator = new \DirectoryIterator($this->moduleFontsDir);
        }
        catch (\UnexpectedValueException $ex) {
            throw new FileNotOpenedException($this->moduleFontsDir, $ex);
        }
        foreach ($iterator as $dirObject) {
            if (!$dirObject->isFile()) {
                continue;
            }
            $file = $dirObject->getFileInfo();
            if (!in_array($file->getExtension(), ['woff', 'woff2', 'ttf', 'eot', 'svg'])) {
                continue;
            }
            if (!copy($file->getRealPath(), self::createPath($this->bitrixFontsDir, $file->getFilename()))) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_CREATE_FILE', [
                    'FILE' => $file->getFilename()
                ]));
            }
        }
    }

    protected function uninstallFonts() {
        $pattern = self::createPath($this->bitrixFontsDir, 'podeli_*');
        foreach (glob($pattern) as $file) {
            if (!unlink($file)) {
                throw new IoException(Loc::getMessage('PODELI.FILE_INSTALLER_CANT_DELETE_FILE', [
                    'FILE' => $file
                ]));
            }
        }
    }
}
