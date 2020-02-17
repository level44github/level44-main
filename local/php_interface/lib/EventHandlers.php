<?php

namespace Level44;


use Bitrix\Main\EventManager;

class EventHandlers
{
    /** @var $instance  EventManager */
    private static $instance = null;

    public static function register()
    {
        if (!self::$instance) {
            self::$instance = EventManager::getInstance();
        }

        self::addEventHandler("sale", "OnBeforeBasketAdd");
        self::addEventHandler("sale", "OnOrderNewSendEmail");
    }

    private static function addEventHandler($moduleId, $eventType)
    {
        if (!$moduleId || !$eventType || !self::$instance) {
            return false;
        }
        return self::$instance->addEventHandler(
            $moduleId,
            $eventType,
            [
                self::class,
                $eventType . "Handler"
            ]
        );
    }

    public static function OnOrderNewSendEmailHandler($orderId, $eventName, &$arFields)
    {
        $order = \Bitrix\Sale\Order::load($arFields["ORDER_ID"]);

        if (!$order) {
            return true;
        }

        $basketItems = $order->getBasket()->getBasketItems();
        $basketItemsContent = "";
        $hostName = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http')
            . '://'
            . $_SERVER['HTTP_HOST'];

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
                    "NAME_EN" => $product["PROPERTY_NAME_EN_VALUE"],
                    "PREVIEW_PICTURE" => $product["PREVIEW_PICTURE"],
                    "DETAIL_PICTURE" => $product["DETAIL_PICTURE"],
                    "DETAIL_PAGE_URL" => $product["DETAIL_PAGE_URL"],
                ];
            }
        }


        foreach ($basketItems as $basketItem) {
            $basketProductId = $basketItem->getProductId();

            $itemName = \Level44\Base::getMultiLang(
                $basketItem->getField("NAME"),
                $arProductsData[$productIds[$basketProductId]]["NAME_EN"]
            );


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

            $imageBase64 = "";

            $image = \CFile::GetFileArray($imageSrc);

            if ($image) {
                if (!$image["CONTENT_TYPE"]) {
                    $image["CONTENT_TYPE"] = "image/jpeg";
                }

                $image["SRC"] = $_SERVER["DOCUMENT_ROOT"] . $image["SRC"];
                $imgBin = fread(fopen($image["SRC"], "r"), filesize($image["SRC"]));
                $imageBase64 = 'data:' . $image["CONTENT_TYPE"] . ';base64,' . base64_encode($imgBin);
            }

            $itemPrice = \CCurrencyLang::CurrencyFormat(
                $basketItem->getPrice('PRICE') * $basketItem->getQuantity(),
                $basketItem->getCurrency(),
                true
            );

            $productUrl = $hostName . $arProductsData[$productIds[$basketProductId]]["DETAIL_PAGE_URL"];

            $arReplace = [
                "#NAME#" => $itemName,
                "#IMG_SRC#" => $imageBase64,
                "#PRICE#" => $itemPrice,
                "#QUANTITY#" => $basketItem->getQuantity(),
                "#PCS#" => $basketItem->getField("MEASURE_NAME"),
                "#COLOR_FIELD#" => Base::getMultiLang("Цвет", "Color"),
                "#SIZE_FIELD#" => Base::getMultiLang("Размер", "Size"),
                "#COLOR#" => $arProperties["COLOR_REF"],
                "#SIZE#" => $arProperties["SIZE_REF"],
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
                "#DELIVERY_ADDRESS#" => $address,
            ];
            $deliveryAddress = str_replace(array_keys($arReplace), array_values($arReplace), $deliveryAddressLayout);
        }

        $arFields["DELIVERY_ADDRESS"] = $deliveryAddress;
        $arFields["YEAR"] = date("Y");

        $titleImgSrc = $_SERVER["DOCUMENT_ROOT"] . Base::getAssetsPath() . "/img/email-title.png";
        if (file_exists($titleImgSrc)) {
            $imgBin = fread(fopen($titleImgSrc, "r"), filesize($titleImgSrc));
            $titleImgBase64 = 'data:image/png;base64,' . base64_encode($imgBin);
        }
        $arFields["EMAIL_TITLE_IMG"] = $titleImgBase64;
    }
}
