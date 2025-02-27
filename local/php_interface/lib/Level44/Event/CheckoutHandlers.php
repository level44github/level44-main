<?php

namespace Level44\Event;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Restrictions\ByPaySystem;
use Bitrix\Sale\PaySystem\Manager;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\ShipmentCollection;
use CIBlockProperty;
use Level44\Base;
use Level44\Delivery;
use Level44\Enums\DeliveryType;
use Level44\PreOrder;

class CheckoutHandlers extends HandlerBase
{
    public static function register()
    {
        static::addEventHandler("sale", "onSaleDeliveryServiceCalculate");
        static::addEventHandler("sale", "OnSaleComponentOrderOneStepDelivery");
        static::addEventHandler("sale", "OnSaleOrderBeforeSaved");

    }


    /**
     * @param $event
     * @return EventResult
     */
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

    /**
     * @param $arResult
     * @param $arUserResult
     * @param $arParams
     * @return void
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws NotImplementedException
     */
    public static function OnSaleComponentOrderOneStepDeliveryHandler(&$arResult, &$arUserResult, $arParams)
    {
        $arResult["DELIVERY"] = array_filter($arResult["DELIVERY"], fn($item) => empty($item["CALCULATE_ERRORS"]));

        $items = array_map(fn($delivery) => [
            'DELIVERY' => $delivery,
            'TYPE'     => Delivery::getType($delivery['ID'])
        ], array_map(Delivery::prepareService(...), $arResult["DELIVERY"]));

        $typesDelivery = [];
        $courierFittingList = [];
        $courierList = [];

        foreach ($items as $item) {
            switch ($item['TYPE']) {
                case DeliveryType::Shop:
                    $typesDelivery["SHOP"] = $item['DELIVERY'];
                    break;
                case DeliveryType::Pickup:
                    $typesDelivery["PICKUP"] = $item['DELIVERY'];
                    break;
                case DeliveryType::CourierFitting:
                    $item['DELIVERY']["IS_COURIER"] = true;
                    $courierFittingList[] = $item['DELIVERY'];
                    break;
                case DeliveryType::Courier:
                    $item['DELIVERY']["IS_COURIER"] = true;
                    $courierList[] = $item['DELIVERY'];
                    break;
            }
        }

        if (!empty($courierFittingList)) {
            $typesDelivery["COURIER_FITTING"] = Delivery::getSuitableCourier($courierFittingList);
        }

        if (!empty($courierList)) {
            $typesDelivery["COURIER"] = Delivery::getSuitableCourier($courierList);
        }


        $arResult["DELIVERY"] = $typesDelivery;
    }

//    public static function OnSaleOrderBeforeSavedHandler(Event $event)
//    {
//        $order = $event->getParameter("ENTITY");
//
//        $shipmentCollection = $order->getShipmentCollection();
//
//        /** @var Shipment $shipment */
//        foreach ($shipmentCollection as $shipment) {
//            if (!$shipment->isSystem()) {
//                $shipment->setBasePriceDelivery(100);
//            }
//        }
//
//        $parameters = [
//            "ENTITY" => $order,
//        ];
//
//        return new EventResult(EventResult::SUCCESS, $parameters);
//    }
}
