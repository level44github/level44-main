<?php

namespace Sale\Delivery\Restrictions;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Restrictions\Base;
use Bitrix\Sale\Shipment;

Loc::loadMessages(__FILE__);

/**
 * Class ByTimeOfDay
 * Ограничение доставки по времени суток
 * Позволяет задать время начала и окончания доступности доставки
 */
class ByTimeOfDay extends Base
{
    /**
     * @return string
     */
    public static function getClassTitle()
    {
        return Loc::getMessage('SALE_DELIVERY_RESTRICTION_BY_TIME_OF_DAY_NAME');
    }

    /**
     * @return string
     */
    public static function getClassDescription()
    {
        return Loc::getMessage('SALE_DELIVERY_RESTRICTION_BY_TIME_OF_DAY_DESCRIPTION');
    }

    /**
     * Проверка ограничения
     * 
     * @param Shipment $shipment
     * @param array $restrictionParams
     * @param int $deliveryId
     * @return bool
     */
    public static function check($shipment, array $restrictionParams, $deliveryId = 0)
    {
        // Если ограничение не активно, пропускаем проверку
        if (empty($restrictionParams['ACTIVE']) || $restrictionParams['ACTIVE'] !== 'Y') {
            return true;
        }

        // Получаем текущее время
        $currentHour = (int)date('G'); // 0-23
        $currentMinute = (int)date('i'); // 0-59
        $currentTime = $currentHour * 60 + $currentMinute; // Время в минутах от начала суток

        // Получаем время начала и окончания
        $timeFrom = !empty($restrictionParams['TIME_FROM']) ? $restrictionParams['TIME_FROM'] : '00:00';
        $timeTo = !empty($restrictionParams['TIME_TO']) ? $restrictionParams['TIME_TO'] : '23:59';

        // Парсим время начала
        $fromParts = explode(':', $timeFrom);
        if (count($fromParts) !== 2) {
            return true; // Если формат неверный, разрешаем доставку
        }
        $fromTime = (int)$fromParts[0] * 60 + (int)$fromParts[1];

        // Парсим время окончания
        $toParts = explode(':', $timeTo);
        if (count($toParts) !== 2) {
            return true; // Если формат неверный, разрешаем доставку
        }
        $toTime = (int)$toParts[0] * 60 + (int)$toParts[1];

        // Проверяем, попадает ли текущее время в разрешенный период
        // Обработка случая, когда период переходит через полночь (например, 22:00 - 02:00)
        if ($fromTime > $toTime) {
            // Период переходит через полночь
            return ($currentTime >= $fromTime || $currentTime <= $toTime);
        } else {
            // Обычный период в пределах одних суток
            return ($currentTime >= $fromTime && $currentTime <= $toTime);
        }
    }

    /**
     * Извлечение параметров из сущности для проверки ограничения
     * 
     * @param \Bitrix\Sale\Internals\Entity $entity
     * @return array
     */
    public static function extractParams(\Bitrix\Sale\Internals\Entity $entity)
    {
        // Для ограничения по времени суток не нужно извлекать параметры из entity
        // Проверка выполняется на основе текущего времени сервера
        return [];
    }

    /**
     * Структура параметров ограничения для админки
     * 
     * @param int $entityId
     * @return array
     */
    public static function getParamsStructure($entityId = 0)
    {
        return [
            'ACTIVE' => [
                'TYPE' => 'Y/N',
                'DEFAULT' => 'N',
                'LABEL' => Loc::getMessage('SALE_DELIVERY_RESTRICTION_BY_TIME_OF_DAY_ACTIVE')
            ],
            'TIME_FROM' => [
                'TYPE' => 'STRING',
                'DEFAULT' => '00:00',
                'LABEL' => Loc::getMessage('SALE_DELIVERY_RESTRICTION_BY_TIME_OF_DAY_TIME_FROM')
            ],
            'TIME_TO' => [
                'TYPE' => 'STRING',
                'DEFAULT' => '23:59',
                'LABEL' => Loc::getMessage('SALE_DELIVERY_RESTRICTION_BY_TIME_OF_DAY_TIME_TO')
            ]
        ];
    }

    /**
     * Валидация параметров перед сохранением
     * 
     * @param array $params
     * @param int $deliveryId
     * @return \Bitrix\Sale\Result
     */
    public static function validateParams($params, $deliveryId = 0)
    {
        $result = parent::validateParams($params, $deliveryId);

        if (!empty($params['ACTIVE']) && $params['ACTIVE'] === 'Y') {
            // Валидация формата времени начала
            if (!empty($params['TIME_FROM'])) {
                if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $params['TIME_FROM'])) {
                    $result->addError(
                        new \Bitrix\Main\Error(
                            Loc::getMessage('SALE_DELIVERY_RESTRICTION_BY_TIME_OF_DAY_ERROR_TIME_FORMAT', ['#FIELD#' => Loc::getMessage('SALE_DELIVERY_RESTRICTION_BY_TIME_OF_DAY_TIME_FROM')])
                        )
                    );
                }
            }

            // Валидация формата времени окончания
            if (!empty($params['TIME_TO'])) {
                if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $params['TIME_TO'])) {
                    $result->addError(
                        new \Bitrix\Main\Error(
                            Loc::getMessage('SALE_DELIVERY_RESTRICTION_BY_TIME_OF_DAY_ERROR_TIME_FORMAT', ['#FIELD#' => Loc::getMessage('SALE_DELIVERY_RESTRICTION_BY_TIME_OF_DAY_TIME_TO')])
                        )
                    );
                }
            }
        }

        return $result;
    }
}

