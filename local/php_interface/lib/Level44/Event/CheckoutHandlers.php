<?php

namespace Level44\Event;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Context;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;
use DalliservicecomDelivery;
use \Level44\CustomKCEClass;
use Level44\Delivery;
use Level44\Enums\DeliveryType;
use Level44\CustomDalliservicecomDelivery;

class CheckoutHandlers extends HandlerBase
{
    /**
     * @throws LoaderException
     */
    public static function register()
    {
        static::addEventHandler("sale", "onSaleDeliveryServiceCalculate");
        static::addEventHandler("sale", "OnSaleComponentOrderOneStepDelivery");
        static::addEventHandler("sale", "OnSaleOrderBeforeSaved");
        static::addEventHandler("sale", "OnSaleOrderSaved");
        static::addEventHandler("sale", "OnSaleShipmentSetField");
        static::addEventHandler("sale", "OnSaleStatusOrderChange", CustomKCEClass::class, sort: 50);
        static::removeKCEOrderStatusHandler();
        static::addEventHandler("sale", "OnSaleStatusShipmentChange", CustomKCEClass::class);

        if (Loader::includeModule('dalliservicecom.delivery') && class_exists(DalliservicecomDelivery::class, false)) {
            static::addEventHandler("sale", "OnSaleBeforeStatusOrderChange", CustomDalliservicecomDelivery::class, compatible: true);
            static::removeDalliOrderStatusHandler();
        }
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
        $typesDelivery = [];
        $courierFittingList = [];
        $courierList = [];

        $res = \CSaleOrderProps::GetList([], ['ACTIVE' => 'Y']);

        $properties = [];
        while ($prop = $res->fetch()) {
            $properties[$prop['ID']] = $prop;
        }

        $request = Context::getCurrent()->getRequest();
        $orderRequest = $request->getPost('order');
        $orderProperties = [];
        foreach ($arUserResult['ORDER_PROP'] as $id => $value) {
            if (!empty($properties[$id]['CODE'])) {
                //Clear address, if it takes from profile, but not passed in ajax request
                if ($properties[$id]['CODE'] === 'ADDRESS') {
                    if (!empty($value) &&
                        (
                            (empty($orderRequest["ORDER_PROP_$id"]) && $orderRequest["is_ajax_post"] === 'Y')
                            || (empty($request->get("ORDER_PROP_$id")) && $request->get('is_ajax_post') === 'Y')
                        )
                    ) {
                        $value = '';
                    }
                }

                $orderProperties[$properties[$id]['CODE']] = $value;
            }
        }

        foreach ($arResult["DELIVERY"] as $delivery) {
            if (!empty($delivery["CALCULATE_ERRORS"]) || $delivery['PERIOD_TEXT'] === 'Connection Error') {
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
            $typesDelivery["COURIER_FITTING"] = Delivery::getSuitableCourier($courierFittingList, $orderProperties);
        }

        if (!empty($courierList)) {
            $typesDelivery["COURIER"] = Delivery::getSuitableCourier($courierList, $orderProperties);
        }

        if (!empty($pickup = $typesDelivery['PICKUP'])) {
            [
                $pickup['PRICE'],
                $pickup['PRICE_FORMATED']
            ] = Delivery::reCalcPrice((int)$pickup['PRICE'], $orderProperties['LOCATION'], (int)$pickup['ID'], $pickup['CURRENCY']);

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
        /** @var Order $order */
        $order = $event->getParameter("ENTITY");
        $location = $order->getPropertyCollection()->getDeliveryLocation();

        $shipmentCollection = $order->getShipmentCollection();
        if (($zipProperty = $order->getPropertyCollection()->getDeliveryLocationZip()) && $location) {

            $locData = LocationTable::getList([
                'filter' => [
                    'CODE'                                         => $location->getValue(),
                    'SALE_LOCATION_LOCATION_EXTERNAL_SERVICE_CODE' => 'ZIP'
                ],
                'select' => ['EXTERNAL.*', 'EXTERNAL.SERVICE.CODE']
            ])->fetch();

            if ($zipValue = $locData['SALE_LOCATION_LOCATION_EXTERNAL_XML_ID']) {
                $zipProperty->setValue($zipValue);
            }
        }

        if ($timeIntervalProperty = $order->getPropertyCollection()->getItemByOrderPropertyCode('TIME_INTERVAL')) {
            [$from, $to] = explode(' - ', $timeIntervalProperty->getValue());

            if (isset($from) && isset($to)) {
                if ($fromProperty = $order->getPropertyCollection()->getItemByOrderPropertyCode('TIME_INTERVAL_FROM')) {
                    $fromProperty->setValue($from);
                }

                if ($toProperty = $order->getPropertyCollection()->getItemByOrderPropertyCode('TIME_INTERVAL_TO')) {
                    $toProperty->setValue($to);
                }
            }
        }

        /** @var Shipment $shipment */
        foreach ($shipmentCollection as $shipment) {
            if (!$shipment->isSystem()) {
                if ($location && $location->getValue()) {
                    [$price] = Delivery::reCalcPrice($shipment->getPrice(), $location->getValue(), $shipment->getDeliveryId());
                    $shipment->setBasePriceDelivery($price);
                }
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

    public static function removeKCEOrderStatusHandler()
    {
        $handlers = \Bitrix\Main\EventManager::getInstance()->findEventHandlers("sale", "OnSaleStatusOrderChange");
        foreach ($handlers as $hKey => $handler) {
            if ($handler['TO_METHOD'] === 'KCEOnSaleOrderSavedHandler') {
                $eventManager = \Bitrix\Main\EventManager::getInstance();
                $eventManager->removeEventHandler('sale', 'OnSaleStatusOrderChange', $hKey);
            }
        }
    }

    public static function removeDalliOrderStatusHandler()
    {
        $handlers = \Bitrix\Main\EventManager::getInstance()->findEventHandlers("sale", "OnSaleBeforeStatusOrderChange");
        foreach ($handlers as $hKey => $handler) {
            if ($handler['TO_CLASS'] === 'DalliservicecomDelivery' && $handler['TO_METHOD'] === 'OnSaleBeforeStatusOrderChange') {
                $eventManager = \Bitrix\Main\EventManager::getInstance();
                $eventManager->removeEventHandler('sale', 'OnSaleBeforeStatusOrderChange', $hKey);
            }
        }
    }

    public static function OnSaleOrderSavedHandler(Event $event)
    {
        /** @var Order $order */
        $order = $event->getParameter("ENTITY");
        $request = Context::getCurrent()->getRequest();
        $userId = $order->getUserId();

        if ($request->getPost('confirmorder') && !empty($userId)) {
            $obUser = new \CUser();
            $obUser->Update($userId, [
                'UF_SUBSCRIBED_TO_NEWSLETTER' => $request->getPost('subscribe') === 'Y' ? '1' : '0'
            ]);
        }

        return new EventResult(EventResult::SUCCESS, $event->getParameters());
    }
}
