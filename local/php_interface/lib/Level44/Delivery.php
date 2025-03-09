<?php

namespace Level44;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Delivery\Restrictions\ByPaySystem;
use Bitrix\Sale\Delivery\Services\Table;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\PaySystem\Manager;
use Bitrix\Sale\PriceMaths;
use Level44\Enums\DeliveryType;
use Sale\Handlers\Delivery\KCEDeliveryHandler;

//Important, depends on the language file local/php_interface/lang/*/lib/Level44/Delivery.php
Loc::loadMessages(__FILE__);

class Delivery
{
    const MSK_LOCATION_CODE = '0000073738';
    const MO_LOCATION_CODE = '0000028025';
    const SPB_LOCATION_CODE = '0000103664';

    /** @var array|null */
    static $paysystems = null;
    /** @var array|null */
    static $deliveries = null;
    static $printLog = '';

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getDeliveries()
    {
        if (!isset(static::$deliveries)) {
            $res = Table::getList(['filter' => ['ACTIVE' => "Y"]]);

            $deliveries = [];

            while ($delivery = $res->fetch()) {
                if (empty($delivery['CODE']) && is_a($delivery['CLASS_NAME'], KCEDeliveryHandler::class, true)) {
                    $delivery['CODE'] = 'kse';
                }

                $deliveries[$delivery["ID"]] = $delivery;
            }

            static::$deliveries = $deliveries;
        }

        return static::$deliveries;
    }

    /**
     * @param string $code
     * @return DeliveryType|null
     * @throws ArgumentException
     */
    public static function getPaySystems(): array
    {
        if (!isset(static::$paysystems)) {
            $res = Manager::getList(['filter' => ['ACTIVE' => "Y"]]);

            $paySystems = [];

            while ($paySystem = $res->fetch()) {
                $paySystems[$paySystem["ACTION_FILE"]] = $paySystem["ID"];
            }

            static::$paysystems = $paySystems;
        }

        return static::$paysystems;
    }

    /**
     * @param string $id
     * @return DeliveryType|null
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws NotImplementedException
     */
    public static function getType(string $id): DeliveryType|null
    {
        $paySystems = static::getPaySystems();
        $deliveries = static::getDeliveries();

        $code = $deliveries[$id]["CODE"];

        if ($code === 'level44:pickup') {
            return DeliveryType::Shop;
        }

        if ($code === 'sdek:pickup') {
            return DeliveryType::Pickup;
        }

        if (in_array($code, ['dalli_service:dalli_courier', 'dalli_service:dalli_cfo', 'kse'])) {
            if (ByPaySystem::check([$paySystems['cloudpayment']], [], $id)) {
                return DeliveryType::Courier;
            } elseif (ByPaySystem::check([$paySystems['cash']], [], $id)) {
                return DeliveryType::CourierFitting;
            }
        }

        return null;
    }

    /**
     * @param array $delivery
     * @return array
     * @throws ArgumentException
     * @throws NotImplementedException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function prepareService(array $delivery): array
    {
        $deliveries = static::getDeliveries();

        if ($deliveries[$delivery['ID']]['CODE'] === 'level44:pickup') {
            $period = $deliveries[$delivery['ID']]['CONFIG']['MAIN']['PERIOD'];

            if (($from = (int)$period['FROM']) && ($to = (int)$period['TO']) && $period['TYPE'] === 'D') {
                $delivery["PERIOD_TEXT"] = "$from-$to " . Loc::getMessage("SHOP_DAYS");
            }
        }

        $delivery["CHECKED"] = $delivery["CHECKED"] === "Y";

        $delivery["DOLLAR_PRICE"] = Base::getDollarPrice($delivery["PRICE"]);
        if (empty($delivery["PRICE_FORMATED"]) || (int)$delivery["PRICE"] <= 0) {
            $delivery["PRICE_FORMATED"] = Loc::getMessage("FREE");
            $delivery["DOLLAR_PRICE"] = false;
        }

        [$period, $price] = [$delivery["PERIOD_TEXT"], $delivery["PRICE_FORMATED"]];

        $delivery["PRICE_PERIOD_TEXT"] = join(', ', array_filter([trim($period), trim($price)]));

        return $delivery;
    }

    /**
     * @param array $courierList
     * @param string $location
     * @return array
     * @throws ArgumentException
     * @throws NotImplementedException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getSuitableCourier(array $courierList, string $location): array
    {
        usort($courierList, fn($a, $b) => $a['PRICE'] <=> $b['PRICE']);

        [$service] = $courierList;

        $someChecked = !empty(array_filter($courierList, fn($item) => $item['CHECKED'] === 'Y'));

        if (!empty($service)) {
            if (empty(static::$printLog)) {
                ob_start();
                $deliveries = static::getDeliveries();

                foreach ($courierList as $courier) {
                    $delivery = !empty($deliveries[$courier["ID"]]['PARENT_ID']) ?
                        $deliveries[$deliveries[$courier["ID"]]['PARENT_ID']] : $deliveries[$courier["ID"]];

                    $name = is_a($delivery['CLASS_NAME'], KCEDeliveryHandler::class, true) ? 'КСЭ' : $delivery['NAME'];

                    echo "Рассчитанная стоимость " . $name . " - " . $courier["PRICE_FORMATED"] . "<br>";
                }

                $delivery = !empty($deliveries[$service["ID"]]['PARENT_ID']) ?
                    $deliveries[$deliveries[$service["ID"]]['PARENT_ID']] : $deliveries[$service["ID"]];

                $name = is_a($delivery['CLASS_NAME'], KCEDeliveryHandler::class, true) ? 'КСЭ' : $delivery['NAME'];

                echo "Выбрана служба: " . $name . "<br>";
                static::$printLog = ob_get_clean();
            }

            if ($service['CHECKED'] !== 'Y' && $someChecked) {
                $service['CHECKED'] = 'Y';
            }

            [$initialPrice, $deliveryId] = [(int)$service['PRICE'], (int)$service['ID']];

            [$price, $priceFormated] = static::reCalcPrice($initialPrice, $location, $deliveryId, $service['CURRENCY']);

            $service['PRICE'] = $price;
            $service['PRICE_FORMATED'] = $priceFormated;

            return $service;
        }

        return [];
    }

    /**
     * @param int $price
     * @param string $location
     * @param int $deliveryId
     * @param string $currency
     * @return array
     * @throws ArgumentException
     * @throws NotImplementedException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function reCalcPrice(int $price, string $location, int $deliveryId, string $currency = ''): array
    {
        $deliveryType = static::getType($deliveryId);
        $calculated = $price;

        if (in_array($deliveryType, [DeliveryType::Courier, DeliveryType::CourierFitting])) {
            $isMoscow = in_array($location, [static::MSK_LOCATION_CODE, static::MO_LOCATION_CODE], true)
                || LocationTable::checkNodeIsParentOfNode(static::MSK_LOCATION_CODE, $location, ['ACCEPT_CODE' => true])
                || LocationTable::checkNodeIsParentOfNode(static::MO_LOCATION_CODE, $location, ['ACCEPT_CODE' => true]);

            $isSpb = $location === static::SPB_LOCATION_CODE
                || LocationTable::checkNodeIsParentOfNode(static::SPB_LOCATION_CODE, $location, ['ACCEPT_CODE' => true]);


            if ($isMoscow) {
                $calculated = 590;
            } elseif ($isSpb) {
                $calculated = 690;
            } else {
                if ($price <= 690) {
                    $calculated = 690;
                } elseif ($price <= 1000) {
                    $calculated = 790;
                } else {
                    $calculated = 890;
                }
            }
        } elseif ($deliveryType === DeliveryType::Pickup) {
            $calculated = 290;
        }

        return [PriceMaths::roundPrecision($calculated), SaleFormatCurrency($calculated, $currency)];
    }
}