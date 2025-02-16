<?php

namespace Level44\Event;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CIBlockProperty;
use Level44\Base;

class BannerHandlers extends HandlerBase
{
    public static function register()
    {
        static::addEventHandler('iblock', 'OnBeforeIBlockElementAdd');
        static::addEventHandler("iblock", "OnBeforeIBlockElementUpdate");

    }

    /**
     * @param $arFields
     * @return bool
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function OnBeforeIBlockElementAddHandler($arFields): bool
    {
        if ($arFields["IBLOCK_ID"] !== Base::BANNER_SLIDES_IBLOCK_ID) {
            return true;
        }

        global $APPLICATION;

        $count = ElementTable::getCount(['IBLOCK_ID' => Base::BANNER_SLIDES_IBLOCK_ID]);

        if ($count >= 5) {
            $APPLICATION->throwException("Максимальное количество слайдов: 5");
            return false;
        }

        return true;
    }

    /**
     * @param $arFields
     * @return bool
     */
    public static function OnBeforeIBlockElementUpdateHandler(&$arFields): bool
    {
        if ($arFields["IBLOCK_ID"] !== Base::BANNER_SLIDES_IBLOCK_ID) {
            return true;
        }

        $res = CIBlockProperty::GetList([], ["IBLOCK_ID" => Base::BANNER_SLIDES_IBLOCK_ID]);

        $properties = [];
        while ($property = $res->GetNext()) {
            $properties[$property["CODE"]] = $property["ID"];
        }

        $link = (string)static::getPropertyValue($arFields["PROPERTY_VALUES"][$properties["LINK_ADDRESS"]]);

        if (!empty($link)) {
            mb_substr($link, -1) === '/' ?: ($link .= '/');
            $link = ltrim($link, '/');
            $link = preg_replace('/\/{2,}/', '/', $link);

            static::setPropertyValue($arFields["PROPERTY_VALUES"][$properties["LINK_ADDRESS"]], $link);
        }

        return true;
    }

    private static function getPropertyValue($property): mixed
    {
        if (!is_array($property)) {
            $property = [];
        }

        return $property[key($property)]["VALUE"];
    }

    private static function setPropertyValue(&$property, $value): void
    {
        if (!is_array($property)) {
            return;
        }

        $property[key($property)]["VALUE"] = $value;
    }

}
