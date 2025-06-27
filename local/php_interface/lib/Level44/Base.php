<?php

namespace Level44;

use Bitrix\Catalog\PriceTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\ObjectPropertyException;
use \Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Registry;
use Level44\Enums\DeliveryType;
use Level44\Event\Exchange1cHandlers;
use Level44\Sale\Basket;
use Bitrix\Highloadblock as HL;
use Level44\Sale\PropertyValue;
use UniPlug\Settings;

class Base
{
    const OFFERS_IBLOCK_ID = 3;
    const CATALOG_IBLOCK_ID = 2;
    const BANNER_SLIDES_IBLOCK_ID = 5;
    const CATALOG_VAT_ID = 3;
    const COLOR_HL_TBL_NAME = "eshop_color_reference";
    const COLOR_GROUP_HL_TBL_NAME = "eshop_color_group_reference";
    const IMAGES_ORIGINAL_HL_TBL_NAME = "images_original";
    const SIZE_HL_TBL_NAME = "size_reference";

    public static $typePage = "";
    public static $sngCountriesId = [];

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
        $asset->addString('<meta  name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">');
        $asset->addString('<link rel="stylesheet" href="' . self::cssAutoVersion(self::getAssetsPath() . "/css/app.css") . '">');
        $asset->addString('<link rel="stylesheet" href="' . self::cssAutoVersion(self::getAssetsPath() . "/css/main.css") . '">');
        if (SITE_TEMPLATE_ID === "checkout") {
            $asset->addCss('https://cdn.jsdelivr.net/npm/suggestions-jquery@22.6.0/dist/css/suggestions.min.css');
        }
    }

    public static function cssAutoVersion($file)
    {
        if (strpos($file, '/') !== 0 || !file_exists($_SERVER['DOCUMENT_ROOT'] . $file)) {
            return $file;
        }

        $modifyTime = filemtime($_SERVER['DOCUMENT_ROOT'] . $file);

        return $file . "?m=$modifyTime";
    }

    public static function loadScripts()
    {
        $asset = Asset::getInstance();
        $asset->addJs(self::getAssetsPath() . "/js/jquery.min.js");
        $asset->addJs(self::getAssetsPath() . "/js/main.js");
        $asset->addJs(self::getAssetsPath() . "/js/app.js");
        $asset->addJs(self::getAssetsPath() . "/js/form.js");
        if (SITE_TEMPLATE_ID === "checkout") {
            $asset->addJs('https://cdn.jsdelivr.net/npm/suggestions-jquery@22.6.0/dist/js/jquery.suggestions.min.js', true);
        }
    }

    public static function isCheckoutPage()
    {
        global $APPLICATION;
        return $APPLICATION->GetCurPage() === SITE_DIR . "checkout/";
    }

    public static function isEnLang($siteId = null)
    {
        if (!empty($siteId)) {
            return $siteId === "en";
        }
        return SITE_ID === "en" && LANGUAGE_ID === "en";
    }

    public static function getMultiLang($ruContent, $enContent, $siteId = null)
    {
        return self::isEnLang($siteId) && !empty($enContent)
            ? $enContent : $ruContent;
    }

    public static function customRegistry()
    {
        try {
            if (Loader::includeModule('sale')) {
                Registry::getInstance(Registry::REGISTRY_TYPE_ORDER)
                    ->set(Registry::ENTITY_BASKET, Basket::class);

                Registry::getInstance(Registry::REGISTRY_TYPE_ORDER)
                    ->set(Registry::ENTITY_PROPERTY_VALUE, PropertyValue::class);
            }
        } catch (\Exception $e) {
        }
    }

    public static function setColorOffers(&$linkedElements, &$currentElement = [])
    {
        $linkedElements = is_array($linkedElements) ? $linkedElements : [];

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
            return $notFormat ? $defaultDollarPrice : self::formatDollar($defaultDollarPrice);
        }

        $dollarPrice = (int)round(\CCurrencyRates::ConvertCurrency($rubPrice, "RUB", "USD"));

        return $notFormat ? $dollarPrice : self::formatDollar($dollarPrice);
    }

    public static function formatDollar($price)
    {
        $price = (int)round($price);
        return "$ {$price}";
    }

    public static function clearImageOriginal($fileId)
    {
        $imagesOriginal = HLWrapper::table(self::IMAGES_ORIGINAL_HL_TBL_NAME);
        $images = $imagesOriginal->getList(["filter" => ["UF_RESIZED_IMAGE_ID" => $fileId]]);
        while ($image = $images->fetch()) {
            $imagesOriginal->delete($image["ID"]);
        }
    }

    public static function setOriginalMorePhoto(&$morePhoto)
    {
        if (!is_array($morePhoto)) {
            return false;
        }

        $fileIds = array_map(function ($item) {
            return $item["ID"];
        }, $morePhoto);

        if (empty($fileIds)) {
            return false;
        }

        $HLOriginalImages = HLWrapper::table(Base::IMAGES_ORIGINAL_HL_TBL_NAME);
        $rsOriginalImages = $HLOriginalImages->getList(["filter" => ['UF_RESIZED_IMAGE_ID' => $fileIds]]);
        $originalImages = [];
        while ($originalImage = $rsOriginalImages->fetch()) {
            $originalImages[$originalImage["UF_RESIZED_IMAGE_ID"]] = $originalImage["UF_IMAGE"];
        }

        $rsFiles = \CFile::GetList([], ["@ID" => implode(",", array_values($originalImages))]);
        $origFiles = [];
        while ($file = $rsFiles->GetNext()) {
            $file["PATH"] = \CFile::GetPath($file["ID"]);
            $origFiles[$file["ID"]] = [
                "ID"     => (int)$file["ID"],
                "SRC"    => $file["PATH"],
                "WIDTH"  => (int)$file["WIDTH"],
                "HEIGHT" => (int)$file["HEIGHT"],
            ];
        }

        foreach ($originalImages as &$originalImage) {
            if (!empty($origFiles[$originalImage])) {
                $originalImage = $origFiles[$originalImage];
            }
        }
        unset($originalImage);

        foreach ($morePhoto as &$morePhotoItem) {
            if (!empty($originalImages[$morePhotoItem["ID"]])) {
                $morePhotoItem = $originalImages[$morePhotoItem["ID"]];
            }
        }
        unset($morePhotoItem);
    }

    public static function getSngCountriesId()
    {
        if (empty(self::$sngCountriesId)) {
            Loader::includeModule("sale");
            $countries = LocationTable::getList([
                'filter' => [
                    '=NAME.LANGUAGE_ID' => "ru",
                    '=NAME.NAME'        => [
                        "Россия",
                        "Беларусь",
                        "Казахстан",
                    ],
                    '=TYPE.CODE'        => 'COUNTRY'
                ],
                'select' => [
                    'ID'
                ]
            ])->fetchAll();

            foreach ($countries as $country) {
                self::$sngCountriesId[] = (int)$country["ID"];
            }
        }

        return self::$sngCountriesId;
    }

    /**
     * The agent function. Moves reserved quantity back to the quantity field for each product
     * for orders which were placed earlier than specific date
     *
     * @return string
     */
    public static function ClearProductReservedQuantity()
    {
        \Level44\Sale\Helpers\ReservedProductCleaner::bind(60);
        return "\Level44\Base::ClearProductReservedQuantity();";
    }

    public static function checkOldPrices(&$arFields)
    {
        global $APPLICATION;
        $request = Context::getCurrent()->getRequest();
        $properties = [];
        $result = PropertyTable::getList([
            "filter" => [
                "IBLOCK_ID" => $arFields["IBLOCK_ID"],
                "CODE"      => ["OLD_PRICE_DOLLAR", "OLD_PRICE", "PRICE_DOLLAR", "CML2_LINK"]
            ]
        ]);

        while ($row = $result->fetch()) {
            $properties[$row["CODE"]] = (int)$row["ID"];
        }

        if ($arFields["IBLOCK_ID"] === Base::OFFERS_IBLOCK_ID) {
            $currentPrice = $request->get("SUBCAT_BASE_PRICE");
            if (is_null($currentPrice)) {
                $currentPrice = $request->get("CAT_BASE_PRICE");
            }

            if (is_null($currentPrice)) {
                return true;
            }
            $currentPrice = (int)$currentPrice;
            if (!empty($arFields["ID"])) {
                $products = \CCatalogSku::getProductList($arFields["ID"]);
                $productId = (int)$products[$arFields["ID"]]["ID"];
            } elseif (!empty($request->get("PRODUCT_ID"))) {
                $productId = (int)$request->get("PRODUCT_ID");
            } else {
                $cml2link = $arFields["PROPERTY_VALUES"][$properties["CML2_LINK"]];

                if (!is_array($cml2link)) {
                    $cml2link = [];
                }

                $productId = (int)$cml2link[key($cml2link)]["VALUE"];
            }

            $product = new Product();
            $ecommerceData = $product->getEcommerceData([$productId]);
            $oldPrice = $ecommerceData[$productId]["prices"]["oldPrice"];

            if ($oldPrice > 0 && $currentPrice > 0 && $currentPrice >= $oldPrice && !Exchange1cHandlers::isSource1C()) {
                $APPLICATION->throwException("Текущая цена должна быть меньше Старой цены");
                return false;
            }
            return true;
        } else {
            $savedPriceDollar = $arFields["PROPERTY_VALUES"][$properties["PRICE_DOLLAR"]];

            if (!is_array($savedPriceDollar)) {
                $savedPriceDollar = [];
            }

            $savedPriceDollar = (int)$savedPriceDollar[key($savedPriceDollar)]["VALUE"];
            $productPriceDollar = $savedPriceDollar;

            $offerIds = \CCatalogSku::getOffersList($arFields["ID"]);

            $offerIds = $offerIds[$arFields["ID"]];
            if (!is_array($offerIds)) {
                $offerIds = [];
            }

            $offerIds = array_keys($offerIds);
            $offerData = [];
            if (!empty($offerIds)) {
                $offerData = PriceTable::getList([
                    "filter" => ["PRODUCT_ID" => $offerIds],
                    "limit"  => 1,
                    "order"  => ["PRICE" => "desc"]
                ])->fetch();
            }
            $offerPrice = (int)$offerData["PRICE"];

            foreach ($arFields["PROPERTY_VALUES"] as $propId => &$property) {
                if (empty($property)) {
                    continue;
                }

                switch ((int)$propId) {
                    case $properties["OLD_PRICE_DOLLAR"]:
                        $value = &$property[key($property)]["VALUE"];
                        $value = (int)$value;

                        if ($value !== 0) {
                            if ($productPriceDollar <= 0) {
                                $APPLICATION->throwException("Старая цена в валюте должна быть заполнена только вместе с Ценой в валюте");
                                return false;
                            }

                            if ($productPriceDollar >= $value) {
                                $APPLICATION->throwException("Старая цена в валюте должна быть больше Цены в валюте");
                                return false;
                            }
                        }
                        break;
                    case $properties["OLD_PRICE"]:
                        $value = &$property[key($property)]["VALUE"];
                        $value = (int)$value;

                        if ($value !== 0 && $offerPrice >= $value && !Exchange1cHandlers::isSource1C()) {
                            $APPLICATION->throwException("Старая цена должна быть больше Текущей цены");
                            return false;
                        }
                        break;
                }
            }
            unset($property);
            return true;
        }
    }

    /**
     * @return string
     */
    public static function cancelUnPaidOrders()
    {
        try {
            $paySystems = \Level44\Delivery::getPaySystems();
            $deliveries = \Level44\Delivery::getDeliveries();
            $courierDeliveries = [];

            if (is_array($deliveries)) {
                $courierDeliveries = array_filter($deliveries, fn($item) => in_array(Delivery::getType($item['ID']), [
                    DeliveryType::Courier,
                    DeliveryType::CourierFitting
                ]));

                $courierDeliveries = array_values(array_map(fn($item) => $item['ID'], $courierDeliveries));
            }

            if (!empty($paySystems['cloudpayments']) && !empty($courierDeliveries)) {
                $orders = Order::getList([
                    'filter' => [
                        "PAY_SYSTEM_ID" => $paySystems['cloudpayments'],
                        "DELIVERY_ID"   => $courierDeliveries,
                        "!STATUS_ID"    => 'CO',
                        "!PAYED"        => 'Y',
                        "<=DATE_INSERT" => ConvertTimeStamp(AddToTimeStamp(["HH" => -4]), 'FULL'),
                        ">=DATE_INSERT" => "25.03.2025 00:00:00",
                    ]
                ])->fetchAll();

                /** @var Order $order */
                foreach ($orders as $order) {
                    \CSaleOrder::StatusOrder($order['ID'], 'CO');
                }
            }
        } catch (\Exception) {
        }

        return "\Level44\Base::cancelUnPaidOrders();";
    }

    /**
     * @param string $colorValue
     * @return array|null
     */
    public static function getColorGroup(string $colorValue): array|null
    {
        $hlBlockRes = HL\HighloadBlockTable::getList([
            'filter' => [
                '=TABLE_NAME' => [Base::COLOR_HL_TBL_NAME, Base::COLOR_GROUP_HL_TBL_NAME]
            ]
        ]);

        $hlBlocks = [];

        while ($hlBlock = $hlBlockRes->fetch()) {
            $hlBlocks[$hlBlock['TABLE_NAME']] = $hlBlock;
        }

        if (empty($hlBlocks[Base::COLOR_HL_TBL_NAME]) || empty($hlBlocks[Base::COLOR_GROUP_HL_TBL_NAME])) {
            return null;
        }

        $colorEntity = HL\HighloadBlockTable::compileEntity($hlBlocks[Base::COLOR_HL_TBL_NAME]);
        $colorEntityClass = $colorEntity->getDataClass();

        $colorRes = $colorEntityClass::getList(
            [
                "select" => [
                    "ID",
                    "UF_GROUP",
                ],
                "filter" => [
                    "UF_XML_ID" => $colorValue
                ],

            ]
        );

        $color = $colorRes->fetch();

        if (empty($color['UF_GROUP'])) {
            return null;
        }

        $colorGroupEntity = HL\HighloadBlockTable::compileEntity($hlBlocks[Base::COLOR_GROUP_HL_TBL_NAME]);
        $colorGroupEntityClass = $colorGroupEntity->getDataClass();

        $colorGroupRes = $colorGroupEntityClass::getList(
            [
                "select" => [
                    "*",
                ],
                "filter" => [
                    "ID" => $color['UF_GROUP']
                ],

            ]
        );

        return $colorGroupRes->fetch() ?: null;
    }

    /**
     * @param int $num
     * @param array $titles
     * @return string|null
     */
    public static function declOfNum(int $num, array $titles): string|null
    {
        $cases = [2, 0, 1, 1, 1, 2];

        return $titles[($num % 100 > 4 && $num % 100 < 20) ? 2 : $cases[min($num % 10, 5)]];
    }
}
