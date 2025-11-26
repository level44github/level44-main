<?php

namespace Level44\Event;

use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Level44\CloudKassir\Api;

/**
 * Обработчики событий для интеграции с CloudKassir
 */
class CloudKassirHandlers extends HandlerBase
{
    /**
     * Регистрация обработчиков событий
     */
    public static function register(): void
    {
        // Событие оплаты заказа
        // Используем compatible режим для поддержки старого формата (ID, VALUE)
        static::addEventHandler("sale", "OnSaleOrderPaid", null, null, 100, true);
        
        // Событие изменения статуса заказа (для обработки возвратов)
        static::addEventHandler("sale", "OnSaleStatusOrderChange");
    }

    /**
     * Обработчик события оплаты заказа
     * Формирует чек в CloudKassir при поступлении оплаты
     *
     * @param Event|Order|int $eventOrOrder Событие, объект заказа или ID заказа
     * @param string|null $value Статус оплаты (Y/N) - для совместимости со старым форматом
     * @return void
     */
    public static function OnSaleOrderPaidHandler($eventOrOrder, ?string $value = null): void
    {
        try {
            $orderId = null;
            $order = null;
            
            // Определяем формат переданных данных
            if ($eventOrOrder instanceof Event) {
                $orderId = $eventOrOrder->getParameter('ID');
                $value = $eventOrOrder->getParameter('VALUE');
            } elseif ($eventOrOrder instanceof Order) {
                $order = $eventOrOrder;
                $orderId = $order->getId();
                if (!$order->isPaid()) {
                    return;
                }
            } elseif (is_numeric($eventOrOrder) || (is_string($eventOrOrder) && is_numeric($eventOrOrder))) {
                $orderId = (int)$eventOrOrder;
            } elseif (is_array($eventOrOrder)) {
                $orderId = $eventOrOrder['ID'] ?? $eventOrOrder['id'] ?? null;
                $value = $eventOrOrder['VALUE'] ?? $eventOrOrder['value'] ?? $value;
            } else {
                if (is_object($eventOrOrder) && method_exists($eventOrOrder, 'getId')) {
                    $order = $eventOrOrder;
                    $orderId = $order->getId();
                } else {
                    return;
                }
            }
            
            // Проверяем, что заказ оплачен (для формата с Event)
            if ($value !== null && $value !== "Y") {
                return;
            }
            
            // Если заказ не был передан, загружаем его
            if (!$order && $orderId) {
                if (!Loader::includeModule('sale')) {
                    return;
                }
                $order = Order::load($orderId);
            }
            
            if (!$order) {
                return;
            }
            
            $orderId = $order->getId();

            // Проверяем, включена ли интеграция с CloudKassir
            if (!self::isEnabled()) {
                return;
            }

            if (!Loader::includeModule('sale')) {
                return;
            }

            // Получаем реально оплаченную сумму (без бонусов)
            $paidAmount = self::getRealPaidAmount($order);

            if ($paidAmount <= 0) {
                return;
            }

            // Получаем внешний платеж (не бонусный)
            $externalPayment = self::getExternalPayment($order);

            if (!$externalPayment || !$externalPayment->isPaid()) {
                return;
            }

            // Проверяем, разрешена ли печать чеков для данной платежной системы
            if (!self::isPaymentSystemAllowed($externalPayment->getPaymentSystemId())) {
                return;
            }

            // Инициализируем API
            $api = new Api();

            // Формируем чек
            $result = $api->createReceipt($order, $externalPayment, $paidAmount);

            if ($result['success']) {
                self::log("Чек успешно создан для заказа #{$orderId}, сумма: {$paidAmount}");
            } else {
                self::log("Ошибка создания чека для заказа #{$orderId}: " . ($result['error'] ?? 'Неизвестная ошибка'), 'ERROR');
            }

        } catch (\Exception $e) {
            self::log("Исключение при обработке оплаты заказа #{$orderId}: " . $e->getMessage(), 'ERROR');
        }
    }

    /**
     * Получает реально оплаченную сумму (без бонусов)
     *
     * @param Order $order Заказ
     * @return float Сумма оплаты без бонусов
     */
    protected static function getRealPaidAmount(Order $order): float
    {
        $paymentCollection = $order->getPaymentCollection();
        $innerPaySystemId = 18; // ID внутренней платежной системы (бонусы)
        $paidAmount = 0.0;

        /** @var Payment $payment */
        foreach ($paymentCollection as $payment) {
            // Пропускаем внутренние платежи (бонусы)
            if ($payment->getPaymentSystemId() == $innerPaySystemId) {
                continue;
            }

            // Учитываем только оплаченные внешние платежи
            if ($payment->isPaid()) {
                $paidAmount += (float)$payment->getSum();
            }
        }

        return $paidAmount;
    }

    /**
     * Получает внешний платеж (не бонусный)
     *
     * @param Order $order Заказ
     * @return Payment|null
     */
    protected static function getExternalPayment(Order $order): ?Payment
    {
        $paymentCollection = $order->getPaymentCollection();
        $innerPaySystemId = 18; // ID внутренней платежной системы (бонусы)

        /** @var Payment $payment */
        foreach ($paymentCollection as $payment) {
            // Пропускаем внутренние платежи (бонусы)
            if ($payment->getPaymentSystemId() == $innerPaySystemId) {
                continue;
            }

            // Возвращаем первый внешний оплаченный платеж
            if ($payment->isPaid()) {
                return $payment;
            }
        }

        return null;
    }

    /**
     * Обработчик события изменения статуса заказа
     * Формирует чек возврата при переводе заказа в статус "Возврат"
     *
     * @param Event $event Событие
     * @return void
     */
    public static function OnSaleStatusOrderChangeHandler(Event $event): void
    {
        try {
            // Проверяем, включена ли интеграция с CloudKassir
            if (!self::isEnabled()) {
                return;
            }

            if (!Loader::includeModule('sale')) {
                return;
            }

            /** @var Order $order */
            $order = $event->getParameter("ENTITY");
            $newStatus = $event->getParameter("VALUE");
            $oldStatus = $event->getParameter("OLD_VALUE");

            // Проверяем, что статус изменился на "Возврат" (RT)
            if ($newStatus !== 'RT' || $newStatus === $oldStatus) {
                return;
            }

            $orderId = $order->getId();

            // Проверяем, что заказ был оплачен (иначе возврат невозможен)
            if (!$order->isPaid()) {
                return;
            }

            // Получаем сумму возврата (только реально оплаченная сумма, без бонусов)
            $refundAmount = self::getRealPaidAmount($order);

            if ($refundAmount <= 0) {
                return;
            }

            // Получаем внешний платеж (не бонусный)
            $externalPayment = self::getExternalPayment($order);

            if (!$externalPayment || !$externalPayment->isPaid()) {
                return;
            }

            // Проверяем, разрешена ли печать чеков для данной платежной системы
            if (!self::isPaymentSystemAllowed($externalPayment->getPaymentSystemId())) {
                return;
            }

            // Инициализируем API
            $api = new Api();

            // Формируем чек возврата
            $result = $api->createReturnReceipt($order, $externalPayment, $refundAmount);

            if ($result['success']) {
                self::log("Чек возврата успешно создан для заказа #{$orderId}, сумма: {$refundAmount}");
            } else {
                self::log("Ошибка создания чека возврата для заказа #{$orderId}: " . ($result['error'] ?? 'Неизвестная ошибка'), 'ERROR');
            }

        } catch (\Exception $e) {
            $orderId = isset($order) ? $order->getId() : 'unknown';
            self::log("Исключение при обработке возврата заказа #{$orderId}: " . $e->getMessage(), 'ERROR');
        }
    }

    /**
     * Проверяет, включена ли интеграция с CloudKassir
     *
     * @return bool
     */
    protected static function isEnabled(): bool
    {
        return \Bitrix\Main\Config\Option::get('level44.cloudkassir', 'enabled', 'N') === 'Y';
    }

    /**
     * Проверяет, разрешена ли печать чеков для данной платежной системы
     *
     * @param int $paymentSystemId ID платежной системы
     * @return bool
     */
    protected static function isPaymentSystemAllowed(int $paymentSystemId): bool
    {
        // Получаем список разрешенных платежных систем из настроек
        $allowedSystems = \Bitrix\Main\Config\Option::get('level44.cloudkassir', 'allowed_payment_systems', '14,17');
        
        // Если настройка пустая, разрешаем все платежные системы (для обратной совместимости)
        if (empty($allowedSystems)) {
            return true;
        }
        
        // Преобразуем строку в массив ID
        $allowedIds = array_map('intval', explode(',', $allowedSystems));
        $allowedIds = array_filter($allowedIds); // Убираем пустые значения
        
        // Если список пуст, разрешаем все (для обратной совместимости)
        if (empty($allowedIds)) {
            return true;
        }
        
        // Проверяем, есть ли ID платежной системы в списке разрешенных
        return in_array($paymentSystemId, $allowedIds, true);
    }

    /**
     * Логирование событий CloudKassir
     *
     * @param string $message Сообщение для лога
     * @param string $level Уровень логирования
     * @return void
     */
    protected static function log(string $message, string $level = 'INFO'): void
    {
        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/cloudkassir.log';
        $date = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$date}] [{$level}] {$message}\n", FILE_APPEND);
    }
}

