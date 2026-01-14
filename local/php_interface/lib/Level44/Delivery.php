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
use cKCE;
use Exception;
use KseService;
use Level44\Enums\DeliveryType;
use Sale\Handlers\Delivery\KCEDeliveryHandler;

//Important, depends on the language file local/php_interface/lang/*/lib/Level44/Delivery.php
Loc::loadMessages(__FILE__);

class Delivery
{
    const MSK_LOCATION_CODE = '0000073738';
    const MO_LOCATION_CODE = '0000028025';
    const SPB_LOCATION_CODE = '0000103664';
    const LO_LOCATION_CODE = '0000028043';

    /** @var array|null */
    static $paysystems = null;
    /** @var array|null */
    static $deliveries = null;

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
                if (empty($delivery['CODE']) && str_contains($delivery['CLASS_NAME'], 'Sale\Handlers\Delivery\KCEDeliveryHandler')) {
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
                $paySystems[$paySystem["CODE"]] = $paySystem["ID"];
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

        if (!isset($deliveries[$id])) {
            return null;
        }

        $code = $deliveries[$id]["CODE"] ?? null;

        if (empty($code)) {
            return null;
        }

        if ($code === 'level44:pickup') {
            return DeliveryType::Shop;
        }

        if ($code === 'level44:courier_fitting') {
            return DeliveryType::CourierFitting;
        }

        if ($code === 'sdek:pickup') {
            return DeliveryType::Pickup;
        }

        if ($code === 'level44:express') {
            return DeliveryType::Express;
        }

        if (in_array($code, ['dalli_service:dalli_courier', 'dalli_service:dalli_cfo', 'kse'])) {
            if (ByPaySystem::check([$paySystems['cloudpayments']], [], $id)) {
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

        if (in_array($deliveries[$delivery['ID']]['CODE'], ['level44:pickup', 'level44:courier_fitting'])) {
            $period = $deliveries[$delivery['ID']]['CONFIG']['MAIN']['PERIOD'];

            if (($from = (int)$period['FROM']) && ($to = (int)$period['TO']) && $period['TYPE'] === 'D') {
                $delivery["PERIOD_TEXT"] = "$from-$to " . Loc::getMessage("SHOP_DAYS");
            }
        }

        //Get max value from period
        if (static::getType($delivery['ID']) === DeliveryType::Courier) {
            $forms = [Loc::getMessage("PERIOD_DAY"), Loc::getMessage("PERIOD_DAYA"), Loc::getMessage("PERIOD_DAYS")];
            [$period, $measure] = explode(' ', trim($delivery["PERIOD_TEXT"]));
            [$from, $to] = explode('-', $period);

            if (is_numeric($from) && is_numeric($to) && in_array(strtolower(trim($measure)), $forms)) {
                $lst = $to % 10;

                if ($lst === 1) {
                    $form = $forms[0];
                } elseif ($lst < 5) {
                    $form = $forms[1];
                } else {
                    $form = $forms[2];
                }

                $delivery["PERIOD_TEXT"] = trim($to) . " $form";
            }
        }

        $delivery["CHECKED"] = $delivery["CHECKED"] === "Y";

        $delivery["DOLLAR_PRICE"] = Base::getDollarPrice($delivery["PRICE"]);
        if (empty($delivery["PRICE_FORMATED"]) || (int)$delivery["PRICE"] <= 0) {
            $delivery["PRICE_FORMATED"] = Loc::getMessage("FREE");
            $delivery["DOLLAR_PRICE"] = false;
        }

        [$period, $price] = [$delivery["PERIOD_TEXT"], $delivery["PRICE_FORMATED"]];


        if (static::getType($delivery['ID']) !== DeliveryType::Express) {
            $delivery["PRICE_PERIOD_TEXT"] = join(', ', array_filter([trim($period), trim($price)]));
        }
        else
        {
            $delivery["PRICE_PERIOD_TEXT"] = trim($price);
        }



        return $delivery;
    }

    /**
     * @param array $courierList
     * @param array $properties
     * @return array
     * @throws ArgumentException
     * @throws NotImplementedException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getSuitableCourier(array $courierList, array $properties): array
    {



        usort($courierList, fn($a, $b) => $a['PRICE'] <=> $b['PRICE']);

        [$service] = $courierList;

        $someChecked = !empty(array_filter($courierList, fn($item) => $item['CHECKED'] === 'Y'));

        if (!empty($service)) {
            $deliveries = static::getDeliveries();
            $deliveryData = $deliveries[$service['ID']];

            if ($service['CHECKED'] !== 'Y' && $someChecked) {
                $service['CHECKED'] = 'Y';
            }

            [$initialPrice, $deliveryId] = [(int)$service['PRICE'], (int)$service['ID']];

            [
                $price,
                $priceFormated
            ] = static::reCalcPrice($initialPrice, $properties['LOCATION'], $deliveryId, $service['CURRENCY']);

            $service['PRICE'] = $price;
            $service['PRICE_FORMATED'] = $priceFormated;


            if ($service['CHECKED'] === 'Y') {
                $slots = static::getSlots($deliveryData['CODE'], $properties);

                if (is_array($slots)) {
                    $service['DELIVERY_DATES'] = $slots;
                    $checkedDate = current(array_filter($slots, fn($slot) => $slot['CHECKED']));

                    if ($checkedDate) {
                        $service['TIME_INTERVALS'] = $checkedDate['intervals'];
                    }
                }
            }

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

        if ($deliveryType === DeliveryType::Courier) {
            $isMoscow = function ($code) {
                return static::includedInRegion($code, self::MSK_LOCATION_CODE) || static::includedInRegion($code, self::MO_LOCATION_CODE);
            };

            $isSpb = function ($code) {
                return static::includedInRegion($code, self::SPB_LOCATION_CODE);
            };


            if ($isMoscow($location)) {
                $calculated = 590;
            } elseif ($isSpb($location)) {
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

    /**
     * @param string $serviceCode
     * @param array $properties
     * @return array|null
     */
    public static function getSlots(string $serviceCode, array $properties): array|null
    {
        $slots = null;



        try {
            if (empty($properties['LOCATION'])) {
                throw new \Exception();
            }

            if ($serviceCode === 'kse') {
                if (!class_exists(KseService::class) || !class_exists(cKCE::class)) {
                    throw new \Exception();
                }

                $location = LocationTable::getByCode($properties['LOCATION'])->fetch();
                $zipTo = KseService::GetZipCode($location['ID']);

                $slots = CustomKCEClass::GetAvailableDeliveryDates($zipTo);
            } elseif (in_array($serviceCode, ['dalli_service:dalli_courier', 'dalli_service:dalli_cfo'])) {
                if (empty($properties['ADDRESS'])) {
                    throw new \Exception();
                }

                if (!class_exists(\DalliservicecomDelivery::class)) {
                    throw new \Exception();
                }

                $zone = static::getDalliZone($properties['LOCATION'], $properties['ADDRESS']);
                $slots = static::getDalliDates($properties['LOCATION'], $zone, $serviceCode);
            } elseif ($serviceCode === 'level44:courier_fitting') {
                $slots = static::getOwnCourierFittingDates();
            }
        } catch (\Exception) {
        }

        if (is_array($slots)) {
            $slots = array_map(function ($item) use ($properties) {
                if ($item['date'] === trim($properties['DELIVERY_DATE'])) {
                    $item['CHECKED'] = true;

                    if (is_array($item['intervals'])) {
                        $item['intervals'] = array_map(function ($interval) use ($properties) {
                            if ($interval['value'] === trim($properties['TIME_INTERVAL'])) {
                                $interval['CHECKED'] = true;
                            }

                            return $interval;
                        }, $item['intervals']);
                    }
                }

                return $item;
            }, $slots);
        }

        return $slots;
    }

    /**
     * @param string $locationCode
     * @param string $address
     * @return int
     * @throws Exception
     */
    public static function getDalliZone(string $locationCode, string $address): int
    {
        $token = \COption::GetOptionString('dalliservicecom.delivery', 'DALLI_TOKEN');
        $location = LocationTable::getByCode($locationCode)->fetch();
        $arLocationTo = \CSaleLocation::GetByID($location['ID'], 'ru');

        $region = $arLocationTo["REGION_NAME"];

        if (!isset($region)) {
            $region = $arLocationTo['CITY_NAME'];
        }

        $xml_data = '<?xml version="1.0" encoding="UTF-8"?>
                <deliverycost module="bitrix">
                    <auth token="' . $token . '"></auth>
                    <partner>DS</partner>
                    <townto>' . $address . '</townto>
                    <oblname>' . $region . '</oblname>
                    <weight>1</weight>
                    <price>0</price>
                    <inshprice>0</inshprice>
					<cashservices>NO</cashservices>
                    <length>1</length>
                    <width>1</width>
                    <height>1</height>
                    <typedelivery>KUR</typedelivery>
                </deliverycost>';

        $arResult = \DalliservicecomDelivery::send_xml($xml_data);

        if ((int)$arResult['deliverycost']['@']['error'] > 0 || (int)$arResult['request']['@']['error'] > 0) {
            throw new \Exception();
        }

        return (int)$arResult['deliverycost']['@']['zone'];
    }

    /**
     * @param $locationCode
     * @param $address
     * @return int
     * @throws Exception
     */
    public static function getDalliDates(string $locationCode, int $zone, string $serviceCode): array
    {
        $token = \COption::GetOptionString('dalliservicecom.delivery', 'DALLI_TOKEN');
        $additionalDays = (int)\COption::GetOptionString('dalliservicecom.delivery', 'DS_COURIER_PLUS_DELIVERY_PERIOD');
        $location = LocationTable::getList([
            'filter' => ['=CODE' => $locationCode, '=NAME.LANGUAGE_ID' => 'ru'],
            'select' => ['CITY_NAME' => 'NAME.NAME']
        ])->fetch();


        $service = null;

        $isMoscow = function ($code) {
            return static::includedInRegion($code, self::MSK_LOCATION_CODE) || static::includedInRegion($code, self::MO_LOCATION_CODE);
        };

        $isSpb = function ($code) {
            return static::includedInRegion($code, self::SPB_LOCATION_CODE) || static::includedInRegion($code, self::LO_LOCATION_CODE);
        };

        if ($serviceCode === 'dalli_service:dalli_courier') {
            if ($isMoscow($locationCode)) {
                $service = 1;
            } elseif ($isSpb($locationCode)) {
                $service = 11;
            }
        } elseif ($serviceCode === 'dalli_service:dalli_cfo') {
            $service = 22;
        }

        if ($service === null) {
            throw new \Exception();
        }

        $xml_data = '<?xml version="1.0" encoding="UTF-8"?>
                <intervals>
                    <auth token="' . $token . '"></auth>
                    <output>dates</output>
                    <zone>' . $zone . '</zone>
                    <town>' . $location['CITY_NAME'] . '</town>
                    <service>' . $service . '</service>
                </intervals>';

        $arResult = \DalliservicecomDelivery::send_xml($xml_data);

        if ((int)$arResult['dates']['@']['error'] > 0 || (int)$arResult['request']['@']['error'] > 0) {
            throw new \Exception();
        }

        $list = $arResult['dates']['#']['date'];

        if (empty($list)) {
            throw new \Exception();
        }

        $deliveryDates = [];

        foreach ($list as $item) {
            $intervals = [];

            foreach ($item['#']['intervals'][0]['#']['interval'] as $intervalItem) {
                $timeMin = $intervalItem['#']['time_min'][0];
                $timeMax = $intervalItem['#']['time_max'][0];

                if (isset($timeMin['#']) && isset($timeMax['#'])) {
                    $from = str_pad($timeMin['#'], 2, '0', STR_PAD_LEFT) . ':00';
                    $to = str_pad($timeMax['#'], 2, '0', STR_PAD_LEFT) . ':00';

                    $intervals[] = [
                        'value' => "$from - $to"
                    ];
                }
            }

            if (!empty($intervals) && !empty($item['@']['value'])) {
                $deliveryDates[] = ["date" => $item['@']['value'], 'intervals' => $intervals];
            }
        }

        return array_slice($deliveryDates, $additionalDays, 5);
    }

    /**
     * @param string $locationCode
     * @param string $regionCode
     * @return bool
     */
    static function includedInRegion(string $locationCode, string $regionCode)
    {
        return $locationCode && $regionCode && (
                $locationCode === $regionCode
                || LocationTable::checkNodeIsParentOfNode($regionCode, $locationCode, ['ACCEPT_CODE' => true])
            );
    }

    /**
     * @return array
     */
    public static function getOwnCourierFittingDates(): array
    {
        $deliveryDates = [];
        $date = date('Y-m-d', strtotime('+2 day'));
        $timeIntervals = [
            ['value' => '10:00 - 14:00',],
            ['value' => '14:00 - 18:00',],
            ['value' => '18:00 - 22:00',],
        ];

        for ($i = 0; $i < 3; $i++) {
            $deliveryDates[] = [
                'date'      => $date,
                'intervals' => $timeIntervals,
            ];

            $date = date('Y-m-d', strtotime("$date +1 day"));
        }

        return $deliveryDates;
    }
}
