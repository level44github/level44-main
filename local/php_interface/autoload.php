<?php

spl_autoload_register(
    static function ($sClass) {
        $sBaseDir = __DIR__.'/lib/';
        $sPath = $sBaseDir.str_replace('\\', DIRECTORY_SEPARATOR, $sClass).'.php';

        if (file_exists($sPath) && is_readable($sPath)) {
            require_once $sPath;
        }
    }
);

spl_autoload_register(
    static function ($sClass) {
        $aClass = explode('_', $sClass);
        if (isset($aClass[2]) && $aClass[2] === 'Interface') {
            list($aClass[1], $aClass[2]) = array($aClass[2], $aClass[1]);
        }

        $sBaseDir = __DIR__.'/lib/';
        $sPath = $sBaseDir.implode(DIRECTORY_SEPARATOR, $aClass).'.php';

        if (file_exists($sPath) && is_readable($sPath)) {
            require_once $sPath;
        }
    }
);
