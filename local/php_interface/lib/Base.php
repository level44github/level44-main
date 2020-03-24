<?php

namespace Level44;

use \Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use Bitrix\Sale\Registry;
use Level44\Sale\Basket;
use Bitrix\Highloadblock as HL;
use UniPlug\Settings;

class Base
{
    const DELIVERY_COURIER = [2, 21, 24];
    const OFFERS_IBLOCK_ID = 3;
    const CATALOG_IBLOCK_ID = 2;
    const COLOR_HL_TBL_NAME = "eshop_color_reference";
    const SIZE_HL_TBL_NAME = "size_reference";

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
        $asset->addCss(self::getAssetsPath() . "/css/main.css");
    }

    public static function loadScripts()
    {
        $asset = Asset::getInstance();
        $asset->addJs(self::getAssetsPath() . "/js/jquery.min.js");
        $asset->addJs(self::getAssetsPath() . "/js/main.js");
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
        return $curPage === SITE_DIR . "checkout/";
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

    public static function getMainBanner($mobile = false)
    {
        $imageUrl = "";
        if (Loader::includeModule("germen.settings")) {
            if ($mobile) {
                $imageId = (int)Settings::get("MAIN_BANNER_MOBILE");
            } else {
                $imageId = (int)Settings::get("MAIN_BANNER");
            }
            $imageUrl = (string)\CFile::GetFileArray($imageId)["SRC"];
        }

        if (empty($imageUrl)) {
            $imageUrl = $mobile ? self::getAssetsPath() . "/img/home-mobile.jpg" : self::getAssetsPath() . "/img/home.jpg";
        }
        return $imageUrl;
    }

    public static function setColorOffers(&$linkedElements, &$currentElement = [])
    {

        if (!empty($currentElement)) {
            $linkedElements = array_filter($linkedElements, function ($item) use ($currentElement) {
                return (int)$item["ID"] !== (int)$currentElement["ID"];
            });

            $linkedElements[$currentElement["ID"]] = $currentElement;
        }

        $needSort = !empty($currentElement);

        $linkedElementsId = array_map(function ($item) {
            return $item["ID"];
        }, $linkedElements);


        if (!empty($linkedElementsId)) {
            $xmlColors = [];
            $linkedProps = \CIBlockElement::GetList(
                [],
                [
                    "=ID" => $linkedElementsId
                ],
                false, false,
                [
                    "ID",
                    "IBLOCK_ID",
                    "PROPERTY_COLOR_REF"
                ]
            );

            while ($linkedProp = $linkedProps->GetNext()) {
                $xmlColors[$linkedProp["ID"]] = $linkedProp["PROPERTY_COLOR_REF_VALUE"];
            }

            foreach ($linkedElements as &$linkedElement) {
                $linkedElement["colorXml"] = $xmlColors[$linkedElement["ID"]];
            }
            unset($linkedElement);

            $colors = array_map(function ($item) {
                return $item["colorXml"];
            }, $linkedElements);
            $colors = array_unique(array_filter($colors));

            if (!empty($colors)) {

                $colorRefTableName = \Level44\Base::COLOR_HL_TBL_NAME;

                $hlblock = HL\HighloadBlockTable::getList([
                    'filter' => [
                        '=TABLE_NAME' => $colorRefTableName
                    ]
                ])->fetch();

                if ($hlblock) {
                    $entity = HL\HighloadBlockTable::compileEntity($hlblock);
                    $entityClass = $entity->getDataClass();

                    $res = $entityClass::getList(
                        [
                            "select" => [
                                "ID",
                                "UF_NAME_EN",
                                "UF_NAME",
                                "UF_FILE",
                                "UF_XML_ID"
                            ],
                            "filter" => [
                                "UF_XML_ID" => $colors
                            ],
                        ]
                    );

                    while ($color = $res->fetch()) {
                        $color["UF_FILE"] = \CFile::GetPath($color["UF_FILE"]);
                        $colorsRef[$color["UF_XML_ID"]] = $color;
                    }
                }
            }

            foreach ($linkedElements as &$linkedElement) {
                $linkedElement["COLOR"] = $colorsRef[$linkedElement["colorXml"]];
                $linkedElement["COLOR_NAME"] = \Level44\Base::getMultiLang(
                    $linkedElement["COLOR"]["UF_NAME"],
                    $linkedElement["COLOR"]["UF_NAME_EN"]
                );
                $linkedElement["ACTIVE"] = (int)$linkedElement["ID"] === (int)$currentElement["ID"];

                if ($linkedElement["ACTIVE"]) {
                    $currentElement["COLOR_NAME"] = $linkedElement["COLOR_NAME"];
                }
            }

            unset($linkedElement);
        }

        $linkedElements = array_filter($linkedElements, function ($item) {
            return !empty($item["COLOR"]);
        });

        if ($needSort) {
            usort($linkedElements, function ($a, $b) {
                if ($a["ID"] == $b["ID"]) {
                    return 0;
                }
                return ($a["ID"] < $b["ID"]) ? -1 : 1;
            });
        }
    }

    public static function getDollarPrice($rubPrice, $defaultDollarPrice = null, $notFormat = false)
    {
        if (!self::isEnLang()) {
            return false;
        }

        $defaultDollarPrice = (int)$defaultDollarPrice;
        if ($defaultDollarPrice > 0) {
            return self::formatDollar($defaultDollarPrice);
        }

        $dollarPrice = (int)round(\CCurrencyRates::ConvertCurrency($rubPrice, "RUB", "USD"));

        return $notFormat ? $dollarPrice : self::formatDollar($dollarPrice);
    }

    public static function formatDollar($price)
    {
        $price = (int)round($price);
        return "$ {$price}";
    }
}
