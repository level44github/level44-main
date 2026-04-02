<?php

namespace Sale\Delivery\Restrictions;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\Restrictions\Base;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\ShipmentItem;

Loc::loadMessages(__FILE__);

/**
 * Скрывает доставку, если у товара свойство NO_EXPRESS (список) = вариант с названием «Да».
 */
class ExcludeIfBasketProductProperty extends Base
{
    public static $easeSort = 250;

    private const PROPERTY_CODE = 'NO_EXPRESS';

    /** Подпись варианта списка в инфоблоке (как в админке у значения свойства) */
    private const LIST_VALUE_YES = 'Да';

    public static function getClassTitle()
    {
        return Loc::getMessage('SALE_DELIVERY_RESTRICTION_EXCLUDE_IF_PRODUCT_PROP_NAME');
    }

    public static function getClassDescription()
    {
        return Loc::getMessage('SALE_DELIVERY_RESTRICTION_EXCLUDE_IF_PRODUCT_PROP_DESC');
    }

    /**
     * @param array $params ['IDS' => int[]]
     */
    public static function check($params, array $restrictionParams, $deliveryId = 0)
    {
        if (empty($restrictionParams['ACTIVE']) || $restrictionParams['ACTIVE'] !== 'Y') {
            return true;
        }

        if (!is_array($params) || empty($params['IDS']) || !is_array($params['IDS'])) {
            return true;
        }

        $checkParent = empty($restrictionParams['CHECK_PARENT']) || $restrictionParams['CHECK_PARENT'] === 'Y';

        foreach ($params['IDS'] as $productId) {
            $productId = (int)$productId;
            if ($productId <= 0) {
                continue;
            }
            foreach (static::getElementIdsForPropertyCheck($productId, $checkParent) as $elementId) {
                if (static::hasNoExpressValueYes($elementId)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return array{IDS: int[]}
     */
    protected static function extractParams(Entity $entity)
    {
        if (!($entity instanceof Shipment)) {
            return ['IDS' => []];
        }

        $ids = [];
        /** @var ShipmentItem $shipmentItem */
        foreach ($entity->getShipmentItemCollection()->getSellableItems() as $shipmentItem) {
            $basketItem = $shipmentItem->getBasketItem();
            if ($basketItem) {
                $ids[] = (int)$basketItem->getProductId();
            }
        }

        // Всегда добавляем корзину заказа: на шаге оформления в отгрузке часто ещё нет строк,
        // а ограничения уже считаются — иначе список ID пустой и ограничение не срабатывает.
        $order = $entity->getCollection()->getOrder();
        if ($order && ($basket = $order->getBasket())) {
            /** @var BasketItem $basketItem */
            foreach ($basket as $basketItem) {
                if ($basketItem->isDelay()) {
                    continue;
                }
                // Не требуем canBuy(): на расчёте доставки он иногда ещё false, а ограничение должно видеть состав корзины.
                $ids[] = (int)$basketItem->getProductId();
            }
        }

        return ['IDS' => array_values(array_unique(array_filter($ids)))];
    }

    /**
     * @return int[]
     */
    protected static function getElementIdsForPropertyCheck(int $productId, bool $checkParent): array
    {
        $ids = [$productId];
        if ($checkParent && Loader::includeModule('catalog')) {
            $info = \CCatalogSku::GetProductInfo($productId);
            if (!empty($info['ID'])) {
                $parentId = (int)$info['ID'];
                if ($parentId > 0 && $parentId !== $productId) {
                    $ids[] = $parentId;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    protected static function hasNoExpressValueYes(int $elementId): bool
    {
        if (!Loader::includeModule('iblock')) {
            return false;
        }

        $iblockId = static::getElementIblockId($elementId);
        if ($iblockId <= 0) {
            return false;
        }

        // Явный IBLOCK_ID надёжнее, чем GetProperty(false, ...).
        // Сначала по CODE; если пусто — полный список и сравнение без регистра (на случай расхождения кода в БД).
        $rows = [];
        $rs = \CIBlockElement::GetProperty($iblockId, $elementId, 'sort', 'asc', ['CODE' => self::PROPERTY_CODE]);
        while ($p = $rs->Fetch()) {
            $rows[] = $p;
        }
        if ($rows === []) {
            $rs = \CIBlockElement::GetProperty($iblockId, $elementId, 'sort', 'asc', []);
            while ($p = $rs->Fetch()) {
                if (strcasecmp((string)($p['CODE'] ?? ''), self::PROPERTY_CODE) === 0) {
                    $rows[] = $p;
                }
            }
        }

        foreach ($rows as $prop) {
            $type = $prop['PROPERTY_TYPE'] ?? '';

            if ($type === 'L') {
                $enumId = (int)($prop['VALUE_ENUM_ID'] ?? 0);
                if ($enumId <= 0 && isset($prop['VALUE']) && is_numeric($prop['VALUE'])) {
                    $enumId = (int)$prop['VALUE'];
                }
                if ($enumId <= 0) {
                    continue;
                }
                if (static::enumLabelIsDa($enumId, $prop)) {
                    return true;
                }
                continue;
            }

            if ($type === 'S' || $type === 'N') {
                $v = static::normalizeYesLabel((string)($prop['VALUE'] ?? ''));
                if (static::labelMeansYes($v)) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function getElementIblockId(int $elementId): int
    {
        $row = \CIBlockElement::GetByID($elementId)->GetNext();
        if (!is_array($row) || empty($row['IBLOCK_ID'])) {
            return 0;
        }

        return (int)$row['IBLOCK_ID'];
    }

    /**
     * Подпись варианта списка «Да» (с учётом сущностей HTML и пробелов).
     */
    private static function enumLabelIsDa(int $enumId, array $propRow): bool
    {
        $label = static::listPropertyValueLabel($propRow, $enumId);

        return static::labelMeansYes($label);
    }

    private static function listPropertyValueLabel(array $prop, int $enumId): string
    {
        foreach (['~VALUE_ENUM', 'VALUE_ENUM', '~VALUE', 'VALUE'] as $key) {
            if (!array_key_exists($key, $prop)) {
                continue;
            }
            $raw = $prop[$key];
            if ($raw === null || $raw === '' || $raw === false) {
                continue;
            }
            if (is_scalar($raw)) {
                $t = static::normalizeYesLabel((string)$raw);
                if ($t !== '') {
                    return $t;
                }
            }
        }

        if ($enumId > 0) {
            $enum = \CIBlockPropertyEnum::GetByID($enumId);
            if (is_array($enum) && array_key_exists('VALUE', $enum) && $enum['VALUE'] !== null && $enum['VALUE'] !== '') {
                return static::normalizeYesLabel((string)$enum['VALUE']);
            }
        }

        return '';
    }

    private static function normalizeYesLabel(string $s): string
    {
        $flags = ENT_QUOTES;
        if (defined('ENT_HTML5')) {
            $flags |= ENT_HTML5;
        }
        $s = html_entity_decode($s, $flags, 'UTF-8');
        $s = trim(preg_replace('/\s+/u', ' ', $s));

        return $s;
    }

    private static function labelMeansYes(string $label): bool
    {
        if ($label === '') {
            return false;
        }
        if ($label === self::LIST_VALUE_YES) {
            return true;
        }
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($label) === mb_strtolower(self::LIST_VALUE_YES);
        }

        return false;
    }

    public static function getParamsStructure($entityId = 0)
    {
        return [
            'ACTIVE' => [
                'TYPE' => 'Y/N',
                'DEFAULT' => 'N',
                'LABEL' => Loc::getMessage('SALE_DELIVERY_RESTRICTION_EXCLUDE_IF_PRODUCT_PROP_ACTIVE'),
            ],
            'CHECK_PARENT' => [
                'TYPE' => 'Y/N',
                'DEFAULT' => 'Y',
                'LABEL' => Loc::getMessage('SALE_DELIVERY_RESTRICTION_EXCLUDE_IF_PRODUCT_PROP_CHECK_PARENT'),
            ],
        ];
    }

    public static function validateParams($params, $deliveryId = 0)
    {
        return new \Bitrix\Sale\Result();
    }
}
