<?php

namespace Level44;

use \Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use Bitrix\Sale\Registry;
use Level44\Sale\Basket;

class Base
{
    const DELIVERY_COURIER = [2, 21, 24];
    const OFFERS_IBLOCK_ID = 3;
    const CATALOG_IBLOCK_ID = 2;

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
        $asset->addString('<link rel="apple-touch-icon" href="' . self::getAssetsPath() . '/img/favicons/apple-touch-icon.png">');
        $asset->addString('<link rel="mask-icon" color="#ffffff" href="' . self::getAssetsPath() . '/img/favicons/safari-pinned-tab.svg">');
        $asset->addString('<link rel="manifest" href="' . self::getAssetsPath() . '/img/favicons/site.webmanifest">');
        $asset->addString('<meta name="msapplication-config" href="' . self::getAssetsPath() . '/img/favicons/browserconfig.xml">');
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

    public static function isCheckoutPage()
    {
        global $APPLICATION;
        $orderId = \Bitrix\Main\Context::getCurrent()
            ->getRequest()
            ->getQuery("ORDER_ID");
        $curPage = $APPLICATION->GetCurPage();
        return $curPage === SITE_DIR . "checkout/" && !isset($orderId);
    }

    public static function isEnLang()
    {
        return SITE_ID === "en" && LANGUAGE_ID === "en";
    }

    public static function getMultiLang($ruContent, $enContent)
    {
        return self::isEnLang() && !empty($enContent)
            ? $enContent : $ruContent;
    }

    public static function customRegistry()
    {
        try {
            \Bitrix\Main\Loader::registerAutoLoadClasses(
                null,
                [
                    "\Level44\Sale\Basket" => "/local/php_interface/lib/Sale/Basket.php",
                    "\Level44\EventHandlers" => "/local/php_interface/lib/EventHandlers.php",
                ]
            );

            if (Loader::includeModule('sale')) {
                Registry::getInstance(Registry::REGISTRY_TYPE_ORDER)
                    ->set(Registry::ENTITY_BASKET, Basket::class);
            }
        } catch (\Exception $e) {
        }
    }
}
