<?php

namespace Level44\Event;

use Level44\Base;
use Bitrix\Main\Context;

class Exchange1cHandlers extends HandlerBase
{
    public static function register()
    {
        static::addEventHandler('iblock', 'OnBeforeIBlockElementAdd');
        static::addEventHandler("iblock", "OnBeforeIBlockSectionAdd");
        static::addEventHandler("iblock", "OnBeforeIBlockElementUpdate");
    }

    public static function isSource1C()
    {
        $request = Context::getCurrent()->getRequest();
        return $request->get('type') === 'catalog'
            && $request->get('mode') === 'import'
            && $request->get('type') === 'catalog'
            && str_contains($GLOBALS['APPLICATION']->GetCurPage(), '1c_exchange.php');
    }

    static function OnBeforeIBlockElementAddHandler(array &$arFields): void
    {
        if (static::isSource1C() && (int)$arFields["IBLOCK_ID"] === Base::CATALOG_IBLOCK_ID) {
            $properties = static::getProperties(Base::CATALOG_IBLOCK_ID);

            if (is_array($traitsProperty = $arFields['PROPERTY_VALUES'][$properties['CML2_TRAITS']])) {
                $oldPrice = current(array_filter($traitsProperty, fn($item) => $item['DESCRIPTION'] === 'СтараяЦена'));

                if ($oldPrice !== false) {
                    $arFields['PROPERTY_VALUES'][$properties['OLD_PRICE']] = [
                        'n0' => ['VALUE' => (int)$oldPrice['VALUE']]
                    ];
                }
            }

            $arFields["ACTIVE"] = 'N';
        }
    }

    static function OnBeforeIBlockElementUpdateHandler(array &$arFields): void
    {
        if (static::isSource1C() && (int)$arFields["IBLOCK_ID"] === Base::CATALOG_IBLOCK_ID) {
            $properties = static::getProperties(Base::CATALOG_IBLOCK_ID);

            if (is_array($traitsProperty = $arFields['PROPERTY_VALUES'][$properties['CML2_TRAITS']])) {
                $oldPrice = current(array_filter($traitsProperty, fn($item) => $item['DESCRIPTION'] === 'СтараяЦена'));

                if ($oldPrice !== false) {
                    $arFields['PROPERTY_VALUES'][$properties['OLD_PRICE']] = [
                        'n0' => ['VALUE' => (int)$oldPrice['VALUE']]
                    ];
                }
            }

            $res = \CIBlockElement::GetElementGroups($arFields["ID"], true, ['ID']);
            $product = \CIBlockElement::GetByID($arFields["ID"])->GetNext();

            $sections = [];

            while ($section = $res->GetNext()) {
                $sections[] = $section['ID'];
            }

            if (!empty($sections)) {
                $arFields['IBLOCK_SECTION'] = $sections;
            }

            $arFields['NAME'] = $product['NAME'] ?: $arFields['NAME'];
            $arFields['CODE'] = $product['CODE'] ?: $arFields['CODE'];
        }
    }

    static function OnBeforeIBlockSectionAddHandler(array &$arFields): void
    {
        if (static::isSource1C() && (int)$arFields["IBLOCK_ID"] === Base::CATALOG_IBLOCK_ID) {
            $arFields["ACTIVE"] = 'N';
        }
    }
}