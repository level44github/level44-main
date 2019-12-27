<?php

use \Bitrix\Main\Page\Asset;

class Helper
{
    const DELIVERY_COURIER = 2;
    public static $typePage = "";

    public static function getAssetsPath()
    {
        return getLocalPath('templates/.default/assets', BX_PERSONAL_ROOT);
    }

    public static function loadAssets()
    {
        $asset = Asset::getInstance();
        $asset->addString('<link rel="shortcut icon" href="' . self::getAssetsPath() . '/img/favicons/favicon.ico" type="image/x-icon">');
        $asset->addString('<link rel="icon" sizes="16x16" href="' . self::getAssetsPath() . '/img/favicons/favicon-16x16.png" type="image/png">');
        $asset->addString('<link rel="icon" sizes="32x32" href="' . self::getAssetsPath() . '/img/favicons/favicon-32x32.png" type="image/png">');
        $asset->addString('<link rel="apple-touch-icon-precomposed" href="' . self::getAssetsPath() . '/img/favicons/apple-touch-icon-precomposed.png">');
        $asset->addString('<link rel="apple-touch-icon" href="' . self::getAssetsPath() . '/img/favicons/apple-touch-icon.png">');
        $asset->addString('<link rel="apple-touch-icon" sizes="57x57" href="' . self::getAssetsPath() . '/img/favicons/apple-touch-icon-57x57.png">');
        $asset->addString('<link rel="apple-touch-icon" sizes="60x60" href="' . self::getAssetsPath() . '/img/favicons/apple-touch-icon-60x60.png">');
        $asset->addString('<link rel="apple-touch-icon" sizes="72x72" href="' . self::getAssetsPath() . '/img/favicons/apple-touch-icon-72x72.png">');
        $asset->addString('<link rel="apple-touch-icon" sizes="76x76" href="' . self::getAssetsPath() . '/img/favicons/apple-touch-icon-76x76.png">');
        $asset->addString('<link rel="apple-touch-icon" sizes="114x114" href="' . self::getAssetsPath() . '/img/favicons/apple-touch-icon-114x114.png">');
        $asset->addString('<link rel="apple-touch-icon" sizes="120x120" href="' . self::getAssetsPath() . '/img/favicons/apple-touch-icon-120x120.png">');
        $asset->addString('<link rel="apple-touch-icon" sizes="144x144" href="' . self::getAssetsPath() . '/img/favicons/apple-touch-icon-144x144.png">');
        $asset->addString('<link rel="apple-touch-icon" sizes="152x152" href="' . self::getAssetsPath() . '/img/favicons/apple-touch-icon-152x152.png">');
        $asset->addString('<link rel="apple-touch-icon" sizes="167x167" href="' . self::getAssetsPath() . '/img/favicons/apple-touch-icon-167x167.png">');
        $asset->addString('<link rel="apple-touch-icon" sizes="180x180" href="' . self::getAssetsPath() . '/img/favicons/apple-touch-icon-180x180.png">');
        $asset->addString('<link rel="apple-touch-icon" sizes="1024x1024" href="' . self::getAssetsPath() . '/img/favicons/apple-touch-icon-1024x1024.png">');
        $asset->addCss(self::getAssetsPath() . "/css/app.css");
    }

    public static function loadScripts()
    {
        $asset = Asset::getInstance();
        $asset->addJs(self::getAssetsPath() . "/js/jquery.min.js");
        $asset->addJs(self::getAssetsPath() . "/js/app.js");
        if (SITE_TEMPLATE_ID === "checkout") {
            $asset->addJs(self::getAssetsPath() . "/js/form.js");
        }
    }
}
