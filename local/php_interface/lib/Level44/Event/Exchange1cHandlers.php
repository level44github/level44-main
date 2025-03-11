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
        static::addEventHandler("iblock", "OnBeforeIBlockSectionUpdate");
        static::addEventHandler("iblock", "OnBeforeIBlockElementUpdate");
    }

    public static function isSource1C()
    {
        $request = Context::getCurrent()->getRequest();
        return $request->get('type') === 'catalog'
            && in_array($request->get('mode'), ['import', 'deactivate'])
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

            $res = \CIBlockElement::GetElementGroups($arFields["ID"], true, ['ID', 'XML_ID']);
            $product = \CIBlockElement::GetByID($arFields["ID"])->GetNext();

            $onlyBitrixSections = [];

            while ($section = $res->GetNext()) {
                if (empty($section['XML_ID'])) {
                    $onlyBitrixSections[] = $section['ID'];
                }
            }

            if (!empty($onlyBitrixSections)) {
                $arFields['IBLOCK_SECTION'] = is_array($arFields['IBLOCK_SECTION']) ? $arFields['IBLOCK_SECTION'] : [];
                $arFields['IBLOCK_SECTION'] = array_values(
                    array_unique(array_merge($arFields['IBLOCK_SECTION'], $onlyBitrixSections), SORT_REGULAR)
                );
            }

            $arFields['NAME'] = $product['~NAME'] ?: $arFields['NAME'];
            $arFields['CODE'] = $product['~CODE'] ?: $arFields['CODE'];
        }
    }

    static function OnBeforeIBlockSectionAddHandler(array &$arFields): void
    {
        if (static::isSource1C() && (int)$arFields["IBLOCK_ID"] === Base::CATALOG_IBLOCK_ID) {
            $arFields["ACTIVE"] = 'N';
        }
    }

    static function OnBeforeIBlockSectionUpdateHandler(array &$arFields): void
    {
        if (static::isSource1C() && (int)$arFields["IBLOCK_ID"] === Base::CATALOG_IBLOCK_ID) {
            $section = \CIBlockSection::GetByID($arFields['ID'])->GetNext();

            if ($arFields['ACTIVE'] !== 'Y' && $section['ACTIVE'] === 'Y' && empty($section['XML_ID'])) {
                $arFields['ACTIVE'] = 'Y';
            }
        }
    }
}