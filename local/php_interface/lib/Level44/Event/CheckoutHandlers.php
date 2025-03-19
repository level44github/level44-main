<?php

namespace Level44\Event;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Shipment;
use \Level44\CustomKCEClass;
use Level44\Delivery;
use Level44\Enums\DeliveryType;

class CheckoutHandlers extends HandlerBase
{
    public static function register()
    {
        static::addEventHandler("sale", "onSaleDeliveryServiceCalculate");
        static::addEventHandler("sale", "OnSaleComponentOrderOneStepDelivery");
        static::addEventHandler("sale", "OnSaleOrderBeforeSaved");
        static::addEventHandler("sale", "OnSaleShipmentSetField");
        static::addEventHandler("sale", "OnSaleStatusOrderChange", CustomKCEClass::class, sort: 50);
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
        $location = $arUserResult['DELIVERY_LOCATION_BCODE'];
        $typesDelivery = [];
        $courierFittingList = [];
        $courierList = [];

        foreach ($arResult["DELIVERY"] as $delivery) {
            if (!empty($delivery["CALCULATE_ERRORS"])) {
                continue;
            }

            switch (Delivery::getType($delivery['ID'])) {
                case DeliveryType::Shop:
                    $typesDelivery["SHOP"] = $delivery;
                    break;
                case DeliveryType::Pickup:
                    $typesDelivery["PICKUP"] = $delivery;
                    break;
                case DeliveryType::CourierFitting:
                    $delivery["IS_COURIER"] = true;
                    $courierFittingList[] = $delivery;
                    break;
                case DeliveryType::Courier:
                    $delivery["IS_COURIER"] = true;
                    $courierList[] = $delivery;
                    break;
            }
        }

        if (!empty($courierFittingList)) {
            $typesDelivery["COURIER_FITTING"] = Delivery::getSuitableCourier($courierFittingList, $location);
        }

        if (!empty($courierList)) {
            $typesDelivery["COURIER"] = Delivery::getSuitableCourier($courierList, $location);
        }

        if (!empty($pickup = $typesDelivery['PICKUP'])) {
            [
                $pickup['PRICE'],
                $pickup['PRICE_FORMATED']
            ] = Delivery::reCalcPrice((int)$pickup['PRICE'], $location, (int)$pickup['ID'], $pickup['CURRENCY']);

            $typesDelivery['PICKUP'] = $pickup;
        }

        $arResult["DELIVERY"] = array_map(Delivery::prepareService(...), array_filter($typesDelivery));
    }

    /**
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws NotSupportedException
     * @throws SystemException
     * @throws NotImplementedException
     */
    public static function OnSaleOrderBeforeSavedHandler(Event $event)
    {
        $order = $event->getParameter("ENTITY");

        $shipmentCollection = $order->getShipmentCollection();

        /** @var Shipment $shipment */
        foreach ($shipmentCollection as $shipment) {
            if (!$shipment->isSystem()) {
                if ($location = $order->getPropertyCollection()->getDeliveryLocation()) {
                    [$price] = Delivery::reCalcPrice($shipment->getPrice(), $location->getValue(), $shipment->getDeliveryId());
                }

                $shipment->setBasePriceDelivery($price);
            }
        }

        $parameters = [
            "ENTITY" => $order,
        ];

        return new EventResult(EventResult::SUCCESS, $parameters);
    }


    /**
     * @param Event $event
     * @return EventResult
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function OnSaleShipmentSetFieldHandler(Event $event)
    {
        ['ENTITY' => $entity, 'NAME' => $name, 'VALUE' => $value] = $event->getParameters();

        if ($name === 'DELIVERY_ID' && !$entity->isSystem() && $entity->isClone()) {
            $deliveries = Delivery::getDeliveries();

            if ($deliveries[$value]['CODE'] === 'kse') {
                $entity->setBasePriceDelivery(0);
                $entity->save();
            }
        }

        return new EventResult(EventResult::SUCCESS);
    }
}
