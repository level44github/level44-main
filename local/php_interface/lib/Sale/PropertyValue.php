<?php

namespace Level44\Sale;

use Bitrix\Main;
use Bitrix\Sale\OrderBase;
use Bitrix\Sale;
use Level44\Base;
use Bitrix\Sale\Location\LocationTable;

class PropertyValue extends Sale\PropertyValue
{
    /**
     * @return null|string
     */
    public function getValue()
    {
        $value = parent::getValue();

        if (static::isOrderView() && $this->getField("CODE") === "LOCATION" && $value) {
            $location = LocationTable::getByCode($value)->fetch();
            $countryId = $location["COUNTRY_ID"];

            if (empty($countryId)) {
                return $value;
            }

            if (!in_array($countryId, Base::getSngCountriesId())) {
                $code = \CSaleLocation::getLocationCODEbyID($countryId);
                if (!empty($code)) {
                    $value = $code;
                }
            }
        }
        return $value;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        $name = parent::getName();

        if (static::isOrderView() && $this->getField("CODE") === "LOCATION" && parent::getValue()) {

            $location = LocationTable::getByCode(parent::getValue())->fetch();
            $countryId = $location["COUNTRY_ID"];
            if (!$countryId) {
                return $name;
            }

            if (!in_array($countryId, Base::getSngCountriesId())) {
                $name = Base::getMultiLang("Страна", "Country", $this->getOrder()->getField("LID"));
            }
        }
        return $name;
    }

    private static function isOrderView()
    {
        global $APPLICATION;
        return strripos($APPLICATION->GetCurPage(), "sale_order_view.php") !== false;
    }
}