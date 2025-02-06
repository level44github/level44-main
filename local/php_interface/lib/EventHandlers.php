<?php

namespace Level44;


use Bitrix\Main\EventManager;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use Bitrix\Sale\Delivery\CalculationResult;
use UniPlug\Settings;

class EventHandlers
{
    /** @var $instance  EventManager */
    private static $instance = null;

    public static function register()
    {
        if (!self::$instance) {
            self::$instance = EventManager::getInstance();
        }

        self::addEventHandler("main", "OnBeforeEventSend");
        self::addEventHandler("main", "OnFileDelete");
        self::addEventHandler("main", "OnAdminIBlockElementEdit", PreOrder::class);
        self::addEventHandler("main", "OnEpilog", PreOrder::class);

        self::addEventHandler("iblock", "OnBeforeIBlockElementUpdate");
        self::addEventHandler("iblock", "OnBeforeIBlockElementAdd");
        self::addEventHandler("iblock", "OnBeforeIBlockUpdate");
        self::addEventHandler("iblock", "OnBeforeIBlockSectionAdd");

        self::addEventHandler("sale", "OnOrderNewSendEmail");
        self::addEventHandler("sale", "onSaleDeliveryServiceCalculate");

        self::addEventHandler("germen.settings", "OnAfterSettingsUpdate");
    }

    private static function addEventHandler($moduleId, $eventType, $class = self::class)
    {
        if (!$moduleId || !$eventType || !self::$instance) {
            return false;
        }
        return self::$instance->addEventHandler(
            $moduleId,
            $eventType,
            [
                $class,
                $eventType . "Handler"
            ]
        );
    }

    public static function OnBeforeEventSendHandler(&$arFields, &$templateData, $context)
    {
        if (!in_array($templateData["EVENT_NAME"],
            [
                "SALE_ORDER_PAID",
                "SALE_NEW_ORDER",
                "CUSTOM_NEW_PREORDER",
            ])) {
            return true;
        }

        $order = \Bitrix\Sale\Order::load($arFields["ORDER_ID"]);

        /** @var $paySystem \Bitrix\Sale\PaySystem\Service */

        $payment = $order?->getPaymentCollection()->current();

        if (!$payment) {
            return false;
        }

        $paySystem = $payment->getPaySystem();
        if (!$paySystem) {
            return false;
        }

        if (!$order) {
            return true;
        }

        $preOrder = $arFields["PRE_ORDER"] === "Y";
        if (!$paySystem->isCash() && $templateData["EVENT_NAME"] === "SALE_NEW_ORDER" && !$preOrder) {
            return false;
        }

        if ($paySystem->isCash() && $templateData["EVENT_NAME"] === "SALE_ORDER_PAID" && !$preOrder) {
            return false;
        }

        $basketItems = $order->getBasket()->getBasketItems();
        $basketItemsContent = "";
        if (!empty($_SERVER['HTTP_HOST'])) {
            $hostName = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http')
                . '://'
                . $_SERVER['HTTP_HOST'];
        } else {
            $hostName = "https://level44.net";
        }


        /** @var \Bitrix\Sale\BasketItem $basketItem */

        $template = <<<HTML
  <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tbody>
                            <tr>
                              <td style="width: 96px">
                                <a href="#PRODUCT_URL#">
                                  <img src='#IMG_SRC#' width='96' height='137' alt="#NAME#">
                                </a>
                              </td>
                              <td style="padding-left: 16px;">
                                <div style="font-weight: bold">#PRICE#</div>
                                #NAME# <br>
                                #OFFER_PROPS#
                                #QUANTITY# #PCS# <br>
                              </td>
                            </tr>
                            <tr><td height="30"></td><td height="30"></td></tr>
                            </tbody>
                          </table>
HTML;

        $offersId = array_map(function ($item) {
            return $item->getProductId();
        }, $basketItems);

        if (empty($offersId)) {
            return false;
        }

        $productList = \CCatalogSKU::getProductList($offersId);

        $productIds = [];
        foreach ($productList as $offerId => $product) {
            $productIds[$offerId] = $product["ID"];
        }

        $products = array_map(function ($productId) {
            return [
                "ID" => $productId
            ];
        }, $productIds);

        \Level44\Base::setColorOffers($products);

        $arProductsData = [];

        if (!empty($productIds)) {
            $resProduct = \CIBlockElement::GetList(
                [],
                [
                    "=ID" => array_values($productIds)
                ],
                false,
                false,
                [
                    "ID",
                    "PROPERTY_NAME_EN",
                    "PREVIEW_PICTURE",
                    "DETAIL_PICTURE",
                    "DETAIL_PAGE_URL"
                ]
            );


            while ($product = $resProduct->GetNext()) {
                $arProductsData[$product["ID"]] = [
                    "ID"              => $product["ID"],
                    "NAME_EN"         => $product["PROPERTY_NAME_EN_VALUE"],
                    "PREVIEW_PICTURE" => $product["PREVIEW_PICTURE"],
                    "DETAIL_PICTURE"  => $product["DETAIL_PICTURE"],
                    "DETAIL_PAGE_URL" => $product["DETAIL_PAGE_URL"],
                ];
            }
        }

        $iBlockId = Base::CATALOG_IBLOCK_ID;
        $arFields["PRODUCT_NAME"] = "";
        $arFields["ADMIN_PRODUCT_URL"] = "";

        foreach ($basketItems as $basketItem) {
            $basketProductId = $basketItem->getProductId();

            $itemName = \Level44\Base::getMultiLang(
                $basketItem->getField("NAME"),
                $arProductsData[$productIds[$basketProductId]]["NAME_EN"]
            );

            if (empty($arFields["PRODUCT_NAME"])) {
                $arFields["PRODUCT_NAME"] = $itemName;
            }

            $curProductId = $arProductsData[$productIds[$basketProductId]]["ID"];

            if ((int)$curProductId <= 0) {
                $curProductId = $basketProductId;
            }

            if (empty($arFields["ADMIN_PRODUCT_URL"])) {
                $arFields["ADMIN_PRODUCT_URL"] = "$hostName/bitrix/admin/iblock_element_edit.php?IBLOCK_ID={$iBlockId}&type=catalog&ID={$curProductId}&lang=ru";
            }

            $offerProps = "";

            $arProperties = [];
            /** @var \Bitrix\Sale\BasketPropertiesCollection $basketPropertyCollection */
            if ($basketPropertyCollection = $basketItem->getPropertyCollection()) {
                /** @var \Bitrix\Sale\BasketPropertyItem $basketPropertyItem */
                foreach ($basketPropertyCollection as $basketPropertyItem) {
                    if (strval(trim($basketPropertyItem->getField('VALUE'))) == "") {
                        continue;
                    }

                    $arProperties[$basketPropertyItem->getField('CODE')] = $basketPropertyItem->getField('VALUE');
                }
            }

            if (!empty($products[$basketProductId])) {
                $arProperties["COLOR_REF"] = $products[$basketProductId]["COLOR_NAME"];
            }

            if (!empty($arProperties["COLOR_REF"])) {
                $offerProps .= "#COLOR_FIELD#: #COLOR# <br>";
            }

            if (!empty($arProperties["SIZE_REF"])) {
                $offerProps .= "#SIZE_FIELD#: #SIZE# <br>";
            }

            $imageSrc = $arProductsData[$productIds[$basketProductId]]["PREVIEW_PICTURE"];
            if (!$imageSrc) {
                $imageSrc = $arProductsData[$productIds[$basketProductId]]["DETAIL_PICTURE"];
            }

            $imageSrc = \CFile::GetFileArray($imageSrc)["SRC"];

            if ($imageSrc) {
                $imageSrc = $hostName . $imageSrc;
            } else {
                $imageSrc = "";
            }

            $itemPrice = \CCurrencyLang::CurrencyFormat(
                $basketItem->getPrice('PRICE') * $basketItem->getQuantity(),
                $basketItem->getCurrency(),
                true
            );

            $productUrl = $hostName . $arProductsData[$productIds[$basketProductId]]["DETAIL_PAGE_URL"];

            $arReplace = [
                "#NAME#"        => $itemName,
                "#IMG_SRC#"     => $imageSrc,
                "#PRICE#"       => $itemPrice,
                "#QUANTITY#"    => $basketItem->getQuantity(),
                "#PCS#"         => $basketItem->getField("MEASURE_NAME"),
                "#COLOR_FIELD#" => Base::getMultiLang("Цвет", "Color"),
                "#SIZE_FIELD#"  => Base::getMultiLang("Размер", "Size"),
                "#COLOR#"       => $arProperties["COLOR_REF"],
                "#SIZE#"        => $arProperties["SIZE_REF"],
                "#PRODUCT_URL#" => $productUrl,
            ];


            $basketItemsContent .= str_replace(
                array_keys($arReplace),
                array_values($arReplace),
                str_replace("#OFFER_PROPS#", $offerProps, $template)
            );
        }

        $arFields["BASKET_ITEMS_CONTENT"] = $basketItemsContent;

        $shipment = $order->getShipmentCollection()
            ->current()
            ->getFieldValues();

        $arFields["DELIVERY_PRICE"] = $shipment["PRICE_DELIVERY"] > 0 ?
            \CCurrencyLang::CurrencyFormat($shipment["PRICE_DELIVERY"], $basketItem->getCurrency(),
                true) : Base::getMultiLang("Бесплатно", "Is free");

        $arFields["DELIVERY_NAME"] = $shipment["DELIVERY_NAME"];


        $addressField = $order->getPropertyCollection()->getAddress();
        $address = !empty($addressField) ? $addressField->getValue() : "";
        $deliveryAddress = "";
        $deliveryAddressLayout = <<<LAYOUT
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="width: 100%">
          <tr><td height="10"></td></tr>
          <tr>
            <td>
              <hr class="divider">
            </td>
          </tr>
          <tr><td height="10"></td></tr>
        </table>

        <table role="presentation" class="main">
          <tr>
            <td class="wrapper">
              <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td>
                    <dvi>#DELIVERY_ADDRESS_FIELD#: #DELIVERY_ADDRESS#</dvi>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
LAYOUT;


        if (!empty($address)) {
            $arReplace = [
                "#DELIVERY_ADDRESS_FIELD#" => Base::getMultiLang("Адрес доставки", "Delivery address"),
                "#DELIVERY_ADDRESS#"       => $address,
            ];
            $deliveryAddress = str_replace(array_keys($arReplace), array_values($arReplace), $deliveryAddressLayout);
        }

        $arFields["DELIVERY_ADDRESS"] = $deliveryAddress;
        $arFields["YEAR"] = date("Y");
        $arFields["PRICE"] = CurrencyFormat($order->getPrice(), "RUB");
        $arFields["EMAIL_TITLE_IMG"] = $hostName . Base::getAssetsPath() . "/img/email-title.png";
        $arFields["PAY_SYSTEM_NAME"] = $paySystem->getField("NAME");
        $arFields["USER_DESCRIPTION"] = $order->getField("USER_DESCRIPTION");
        $arFields["ADMIN_LINK"] = "{$hostName}/bitrix/admin/sale_order_view.php?ID={$order->getId()}&lang=ru";
        $arFields["DELIVERY_DATA"] = '<strong style="font-weight: bold;">#DELIVERY_PRICE#</strong> <br>
                      #DELIVERY_NAME#';
        if ($preOrder) {
            $arFields["DELIVERY_DATA"] = "";
            $arFields["ORDER_USER"] = "";
        }
    }

    public static function OnAfterSettingsUpdateHandler()
    {
        $existAgent = \CAgent::GetList(
            [],
            [
                "NAME" => "\Level44\Base::ClearProductReservedQuantity();"
            ])->GetNext();

        Loader::includeModule("germen.settings");
        $minPeriod = (int)Settings::get("RESERVE_CLEAR_PERIOD");
        if ($minPeriod <= 0) {
            if (!empty($existAgent)) {
                \CAgent::Delete($existAgent["ID"]);
            }
            return true;
        }

        $secPeriod = $minPeriod * 60;
        if (empty($existAgent)) {
            \CAgent::AddAgent("\Level44\Base::ClearProductReservedQuantity();", "", "N", $secPeriod, "", "Y");
        } elseif ((int)$existAgent["AGENT_INTERVAL"] !== $secPeriod) {
            \CAgent::Delete($existAgent["ID"]);
            \CAgent::AddAgent("\Level44\Base::ClearProductReservedQuantity();", "", "N", $secPeriod, "", "Y");
        }

        return true;
    }

    public static function OnFileDeleteHandler($arFile)
    {
        \Level44\Base::clearImageOriginal($arFile["ID"]);
    }

    public static function OnOrderNewSendEmailHandler($orderId, &$eventName, $fields)
    {
        if (PreOrder::isPreOrder($orderId)) {
            $eventName = "CUSTOM_NEW_PREORDER";
        }
    }

    public static function onSaleDeliveryServiceCalculateHandler($event)
    {
        /** @var CalculationResult $result */
        $result = $event->getParameter("RESULT");
        $result->setDeliveryPrice(floor($result->getDeliveryPrice()));
        $parameters = [
            "RESULT" => $result,
        ];
        return new EventResult(EventResult::SUCCESS, $parameters);
    }

    public static function OnBeforeIBlockElementAddHandler(&$arFields)
    {
        Exchange1C::handleAddProduct($arFields);

        return Base::checkOldPrices($arFields);
    }

    public static function OnBeforeIBlockSectionAddHandler(&$arFields)
    {
        Exchange1C::handleAddSection($arFields);

        return true;
    }

    public static function OnBeforeIBlockElementUpdateHandler(&$arFields)
    {
        Exchange1C::handleUpdateProduct($arFields);

        return Base::checkOldPrices($arFields);
    }

    public static function OnBeforeIBlockUpdateHandler(&$arFields)
    {
        $iBlocks = [
            Base::CATALOG_IBLOCK_ID,
            Base::OFFERS_IBLOCK_ID
        ];

        if (in_array($arFields["ID"], $iBlocks) && is_array($arFields["LID"]) && array_key_last($arFields["LID"])) {
            if ($arFields["LID"][array_key_last($arFields["LID"])] !== 's1') {
                $arFields["LID"] = array_reverse($arFields["LID"]);
            }
        }

        return true;
    }
}
