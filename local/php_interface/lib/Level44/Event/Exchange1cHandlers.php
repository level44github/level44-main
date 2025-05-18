<?php

namespace Level44\Event;

use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Event;
use Bitrix\Catalog\Model;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
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
        static::addEventHandler("catalog", "Bitrix\Catalog\Model\Product::OnAfterAdd", method: 'OnProductSaveHandler');
        static::addEventHandler("catalog", "Bitrix\Catalog\Model\Product::OnAfterUpdate", method: 'OnProductSaveHandler');
    }

    public static function isSource1C()
    {
        $request = Context::getCurrent()->getRequest();
        return $request->get('type') === 'catalog'
            && in_array($request->get('mode'), ['import', 'deactivate'])
            && str_contains($GLOBALS['APPLICATION']->GetCurPage(), '1c_exchange.php');
    }

    /**
     * @param array $arFields
     * @return void
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
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
            unset($arFields['DETAIL_TEXT']);

            $onModerationEnum = PropertyEnumerationTable::getList(
                ['filter' => ['PROPERTY_ID' => $properties['ON_MODERATION']]]
            )->fetch();

            if (!empty($onModerationEnum['ID'])) {
                $arFields['PROPERTY_VALUES'][$properties['ON_MODERATION']] = [
                    'n0' => ['VALUE' => $onModerationEnum['ID']]
                ];
            }
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

            $product = \CIBlockElement::GetList([], ['ID' => $arFields["ID"]], false, false, [
                'ID',
                'NAME',
                'CODE',
                'ACTIVE',
                'DETAIL_TEXT',
                'PROPERTY_ON_MODERATION'
            ])->GetNext();

            if (is_array($arFields['IBLOCK_SECTION'])) {
                $res = \CIBlockElement::GetElementGroups($arFields["ID"], true, ['ID', 'XML_ID']);
                $onlyBitrixSections = [];

                while ($section = $res->GetNext()) {
                    if (empty($section['XML_ID'])) {
                        $onlyBitrixSections[] = $section['ID'];
                    }
                }

                if (!empty($onlyBitrixSections)) {
                    $arFields['IBLOCK_SECTION'] = array_values(
                        array_unique(array_merge($arFields['IBLOCK_SECTION'], $onlyBitrixSections), SORT_REGULAR)
                    );
                }
            }

            $arFields['NAME'] = $product['~NAME'] ?: $arFields['NAME'];
            $arFields['CODE'] = $product['~CODE'] ?: $arFields['CODE'];
            $arFields['DETAIL_TEXT_TYPE'] = $product['DETAIL_TEXT_TYPE'] ?: $arFields['DETAIL_TEXT_TYPE'];
            $arFields['DETAIL_TEXT'] = $product['~DETAIL_TEXT'];

            if ($arFields['ACTIVE'] === 'Y' && $product['ACTIVE'] !== 'Y' && $product['PROPERTY_ON_MODERATION_VALUE'] === 'Y') {
                $arFields['ACTIVE'] = 'N';
            }
        }
    }

    static function OnBeforeIBlockSectionAddHandler(array &$arFields): void
    {
        if (static::isSource1C() && (int)$arFields["IBLOCK_ID"] === Base::CATALOG_IBLOCK_ID) {
            $arFields["ACTIVE"] = 'N';
            $arFields['UF_ON_MODERATION'] = '1';
        }
    }

    static function OnBeforeIBlockSectionUpdateHandler(array &$arFields): void
    {
        if ((int)$arFields["IBLOCK_ID"] === Base::CATALOG_IBLOCK_ID) {
            if (static::isSource1C()) {
                $section = \CIBlockSection::GetList([], [
                    'ID'        => $arFields['ID'],
                    'IBLOCK_ID' => Base::CATALOG_IBLOCK_ID
                ], false, ['ID', 'ACTIVE', 'XML_ID', 'UF_ON_MODERATION'])->GetNext();

                if ($arFields['ACTIVE'] !== 'Y' && $section['ACTIVE'] === 'Y' && empty($section['XML_ID'])) {
                    $arFields['ACTIVE'] = 'Y';
                }

                if ($arFields['ACTIVE'] === 'Y' && $section['ACTIVE'] !== 'Y' && $section['UF_ON_MODERATION']) {
                    $arFields['ACTIVE'] = 'N';
                }
            } else {
                if ($arFields['ACTIVE'] === 'Y') {
                    $arFields['UF_ON_MODERATION'] = '0';
                }
            }
        }
    }

    static function OnProductSaveHandler(Event $event): Model\EventResult
    {
        $result = new Model\EventResult();

        if (!static::isSource1C()) {
            return $result;
        }

        $id = $event->getParameter('id');
        $externalFields = $event->getParameter('external_fields');

        if ((int)$externalFields['IBLOCK_ID'] === Base::OFFERS_IBLOCK_ID && ($productInfo = \CCatalogSku::GetProductInfo($id))) {
            $productsOffers = \CCatalogSku::getOffersList($productInfo['ID'], fields: ['QUANTITY', 'ACTIVE']);

            $totalQuantity = 0;

            if (is_array($offers = $productsOffers[$productInfo['ID']])) {
                foreach ($offers as $offer) {
                    if ($offer['ACTIVE'] === 'Y') {
                        $totalQuantity += (int)$offer['QUANTITY'];
                    }
                }
            }

            $product = \CIBlockElement::GetByID($productInfo['ID'])->GetNext();

            if ($product['ACTIVE'] === 'Y' && $totalQuantity <= 0) {
                (new \CIBlockElement)->Update($productInfo['ID'], ['ACTIVE' => 'N']);
            }

            if ($product['ACTIVE'] === 'N' && $totalQuantity > 0) {
                (new \CIBlockElement)->Update($productInfo['ID'], ['ACTIVE' => 'Y']);
            }
        }

        return $result;
    }
}