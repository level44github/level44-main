<?php

namespace Level44\CloudKassir;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;

/**
 * Класс для работы с API CloudKassir
 * Документация: https://cloudkassir.ru/
 */
class Api
{
    /** @var string URL API CloudKassir */
    protected string $apiUrl;

    /** @var string Public ID для авторизации */
    protected string $publicId;

    /** @var string API Secret для авторизации */
    protected string $apiSecret;

    /** @var HttpClient HTTP клиент */
    protected HttpClient $httpClient;

    /**
     * Конструктор
     */
    public function __construct()
    {
        // Получаем настройки из опций модуля
        // CloudKassir использует API CloudPayments для формирования чеков
        // Правильный endpoint для создания чека: https://api.cloudpayments.ru/kkt/receipt
        $defaultUrl = Option::get('level44.cloudkassir', 'api_url', '');
        if (empty($defaultUrl)) {
            $defaultUrl = 'https://api.cloudpayments.ru/kkt/receipt';
        }
        $this->apiUrl = $defaultUrl;
        $this->publicId = Option::get('level44.cloudkassir', 'public_id', '');
        $this->apiSecret = Option::get('level44.cloudkassir', 'api_secret', '');

        $this->httpClient = new HttpClient([
            'socketTimeout' => 30,
            'streamTimeout' => 30,
        ]);

        // Базовая HTTP авторизация (PublicId:ApiSecret в base64)
        // CloudKassir использует Basic Auth с PublicId и ApiSecret
        if (!empty($this->publicId) && !empty($this->apiSecret)) {
            $authString = base64_encode($this->publicId . ':' . $this->apiSecret);
            $this->httpClient->setHeader('Authorization', 'Basic ' . $authString);
        }

        $this->httpClient->setHeader('Content-Type', 'application/json');
    }

    /**
     * Формирование чека возврата
     *
     * @param Order $order Заказ
     * @param Payment $payment Платеж (внешний, не бонусный)
     * @param float $refundAmount Сумма возврата
     * @return array Результат операции
     */
    public function createReturnReceipt(Order $order, Payment $payment, float $refundAmount): array
    {
        // Используем тот же метод, но с типом IncomeReturn
        return $this->createReceiptInternal($order, $payment, $refundAmount, 'IncomeReturn');
    }

    /**
     * Формирование чека прихода
     *
     * @param Order $order Заказ
     * @param Payment $payment Платеж (внешний, не бонусный)
     * @param float $paidAmount Сумма оплаты (без бонусов)
     * @return array Результат операции
     */
    public function createReceipt(Order $order, Payment $payment, float $paidAmount): array
    {
        return $this->createReceiptInternal($order, $payment, $paidAmount, 'Income');
    }

    /**
     * Внутренний метод для формирования чека (приход или возврат)
     *
     * @param Order $order Заказ
     * @param Payment $payment Платеж (внешний, не бонусный)
     * @param float $amount Сумма
     * @param string $type Тип чека: Income или IncomeReturn
     * @return array Результат операции
     */
    protected function createReceiptInternal(Order $order, Payment $payment, float $amount, string $type = 'Income'): array
    {
        try {
            if (empty($this->publicId) || empty($this->apiSecret)) {
                return [
                    'success' => false,
                    'error' => 'Не указаны Public ID или API Secret. Настройте модуль в административной панели.',
                ];
            }

            $inn = $this->getInn();
            if (empty($inn)) {
                return [
                    'success' => false,
                    'error' => 'Не указан ИНН организации. Настройте модуль в административной панели.',
                ];
            }

            if ($amount <= 0) {
                return [
                    'success' => false,
                    'error' => 'Сумма должна быть больше нуля.',
                ];
            }

            // Получаем данные заказа
            $basket = $order->getBasket();
            $items = [];

            // Получаем сумму бонусного платежа (ID платежной системы = 18)
            $bonusPaymentAmount = $this->getBonusPaymentAmount($order);
            
            // Сначала собираем все товары и вычисляем общую сумму товаров (без доставки)
            $productItems = [];
            $totalProductsAmount = 0.0;
            
            foreach ($basket->getBasketItems() as $basketItem) {
                $itemPrice = $basketItem->getPrice();
                $itemQuantity = $basketItem->getQuantity();
                $itemAmount = $itemPrice * $itemQuantity;
                $totalProductsAmount += $itemAmount;

                // Получаем ставку НДС
                $vatRate = $this->getVatRate($basketItem);

                $item = [
                    'label' => $basketItem->getField('NAME'),
                    'price' => $itemPrice,
                    'quantity' => $itemQuantity,
                    'amount' => $itemAmount,
                    'vat' => $vatRate,
                    'object' => $this->getPaymentObject(), // Предмет расчета
                    'method' => $this->getPaymentMethod(), // Способ расчета
                ];

                // Добавляем дополнительные свойства, если есть
                foreach ($basketItem->getPropertyCollection() as $property) {
                    if ($property->getField('CODE') === 'SPIC') {
                        $item['spic'] = $property->getField('VALUE');
                    }
                    if ($property->getField('CODE') === 'PACKAGE_CODE') {
                        $item['packageCode'] = $property->getField('VALUE');
                    }
                }

                $productItems[] = $item;
            }

            // Если есть бонусная оплата, пропорционально уменьшаем суммы товаров
            if ($bonusPaymentAmount > 0 && $totalProductsAmount > 0) {
                // Вычисляем коэффициент уменьшения
                // Ограничиваем бонусную оплату суммой товаров (на случай, если бонусов больше)
                $effectiveBonusAmount = min($bonusPaymentAmount, $totalProductsAmount);
                $reductionCoefficient = ($totalProductsAmount - $effectiveBonusAmount) / $totalProductsAmount;
                
                // Применяем коэффициент к каждому товару
                foreach ($productItems as &$item) {
                    $item['amount'] = $item['amount'] * $reductionCoefficient;
                    $item['price'] = $item['amount'] / $item['quantity'];
                }
                unset($item);
            }

            // Форматируем суммы товаров для чека
            foreach ($productItems as $item) {
                $items[] = [
                    'label' => $item['label'],
                    'price' => number_format($item['price'], 2, '.', ''),
                    'quantity' => $item['quantity'],
                    'amount' => number_format($item['amount'], 2, '.', ''),
                    'vat' => $item['vat'],
                    'object' => $item['object'],
                    'method' => $item['method'],
                ] + (isset($item['spic']) ? ['spic' => $item['spic']] : [])
                  + (isset($item['packageCode']) ? ['packageCode' => $item['packageCode']] : []);
            }

            // Добавляем доставку, если есть (доставка не уменьшается на бонусную оплату)
            $deliveryPrice = $order->getDeliveryPrice();
            if ($deliveryPrice > 0) {
                $deliveryVat = $this->getDeliveryVatRate($order, $payment);
                $items[] = [
                    'label' => 'Доставка',
                    'price' => number_format($deliveryPrice, 2, '.', ''),
                    'quantity' => 1,
                    'amount' => number_format($deliveryPrice, 2, '.', ''),
                    'vat' => $deliveryVat,
                    'object' => 4, // Услуга
                    'method' => $this->getPaymentMethod(),
                ];
            }

            // Получаем контакты клиента
            $propertyCollection = $order->getPropertyCollection();
            $email = $propertyCollection->getUserEmail() ? $propertyCollection->getUserEmail()->getValue() : '';
            $phone = $propertyCollection->getPhone() ? $propertyCollection->getPhone()->getValue() : '';

            // Формируем данные для запроса в формате API CloudKassir
            // CloudKassir использует API CloudPayments для формирования чеков
            // Временно для тестирования добавляем префикс "1111-" к ID заказа
            $invoiceId = '1111-' . $order->getId();
            
            // Получаем ИНН и убираем пробелы
            $inn = trim($this->getInn());
            if (empty($inn)) {
                return [
                    'success' => false,
                    'error' => 'ИНН не указан или пустой',
                ];
            }
            
            // Получаем номер документа прихода для TransactionId
            // Сначала пробуем PAY_VOUCHER_NUM (используется в PayPal и других системах)
            // Если не заполнен, пробуем PS_INVOICE_ID (используется в YandexCheckout)
            $transactionId = null;
            
            // Пробуем получить через getField
            $payVoucherNum = $payment->getField('PAY_VOUCHER_NUM');
            $psInvoiceId = $payment->getField('PS_INVOICE_ID');
            
            // Если getField не работает, пробуем через getFieldValues
            if (empty($payVoucherNum) && empty($psInvoiceId)) {
                $paymentFields = $payment->getFieldValues();
                $payVoucherNum = $paymentFields['PAY_VOUCHER_NUM'] ?? null;
                $psInvoiceId = $paymentFields['PS_INVOICE_ID'] ?? null;
            }
            
            // Приоритет у PAY_VOUCHER_NUM, если он заполнен
            if (!empty($payVoucherNum) && trim($payVoucherNum) !== '' && $payVoucherNum !== '0') {
                $transactionId = trim((string)$payVoucherNum);
            } elseif (!empty($psInvoiceId) && trim($psInvoiceId) !== '' && $psInvoiceId !== '0') {
                $transactionId = trim((string)$psInvoiceId);
            }
            
            // Подготавливаем TransactionId, если он заполнен
            $transactionIdValue = $transactionId;
            
            $requestData = [
                'Inn' => $inn, // ИНН организации (обязательно, без пробелов)
                'Type' => $type, // Тип чека: Income (приход) или IncomeReturn (возврат прихода)
                'CustomerReceipt' => [
                    'Items' => $items,
                    'taxationSystem' => $this->getTaxationSystem(),
                    'email' => !empty($email) ? $email : null,
                    'phone' => $phone ? $this->formatPhone($phone) : null,
                ],
                'InvoiceId' => $invoiceId,
                'AccountId' => (string)$order->getUserId(),
            ];
            
            // Добавляем TransactionId, если он заполнен
            if ($transactionIdValue !== null) {
                $requestData['TransactionId'] = $transactionIdValue;
                $this->log("TransactionId установлен для заказа #{$order->getId()}: {$transactionIdValue}");
            } else {
                // Временное логирование для отладки
                $paymentFields = $payment->getFieldValues();
                $this->log("TransactionId не установлен для заказа #{$order->getId()}. PAY_VOUCHER_NUM: " . 
                    var_export($paymentFields['PAY_VOUCHER_NUM'] ?? null, true) . 
                    ", PS_INVOICE_ID: " . var_export($paymentFields['PS_INVOICE_ID'] ?? null, true));
            }
            
            // Удаляем null значения из CustomerReceipt для чистоты запроса
            if ($requestData['CustomerReceipt']['email'] === null) {
                unset($requestData['CustomerReceipt']['email']);
            }
            if ($requestData['CustomerReceipt']['phone'] === null) {
                unset($requestData['CustomerReceipt']['phone']);
            }

            // Отправляем запрос
            $response = $this->post($this->apiUrl, $requestData);
            
            // API CloudKassir возвращает успешный ответ без поля Success
            // Проверяем наличие ошибок
            if (isset($response['Success']) && $response['Success'] === false) {
                $errorMessage = $response['Message'] ?? $response['error'] ?? 'Неизвестная ошибка';
                $this->log("Ошибка создания {$receiptTypeName} для заказа #{$order->getId()}: {$errorMessage}", 'ERROR');
                return [
                    'success' => false,
                    'error' => $errorMessage,
                    'response' => $response,
                ];
            } elseif (isset($response['error']) || isset($response['errors'])) {
                $errorMessage = $response['error'] ?? (is_array($response['errors']) ? implode(', ', $response['errors']) : 'Неизвестная ошибка');
                $this->log("Ошибка создания {$receiptTypeName} для заказа #{$order->getId()}: {$errorMessage}", 'ERROR');
                return [
                    'success' => false,
                    'error' => $errorMessage,
                    'response' => $response,
                ];
            } else {
                // Успешный ответ
                $this->log("{$receiptTypeName} успешно создан для заказа #{$order->getId()}, сумма: {$amount}");
                return [
                    'success' => true,
                    'data' => $response,
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Получает ставку НДС для товара
     *
     * @param \Bitrix\Sale\BasketItem $basketItem
     * @return int|null Ставка НДС в процентах (0, 10, 20) или null
     */
    protected function getVatRate($basketItem): ?int
    {
        // Пытаемся получить НДС из поля товара
        $vatRateField = $basketItem->getField('VAT_RATE');
        if (!is_null($vatRateField)) {
            return (int)($vatRateField * 100);
        }

        // Пытаемся получить НДС из VAT_ID
        $vatId = $basketItem->getField('VAT_ID');
        if ($vatId && Loader::includeModule('catalog')) {
            $vat = \Bitrix\Catalog\VatTable::getById($vatId)->fetch();
            if ($vat && isset($vat['RATE'])) {
                return (int)($vat['RATE'] * 100);
            }
        }

        // Используем значение по умолчанию из настроек
        $vatRate = Option::get('level44.cloudkassir', 'vat_rate', '20');
        return $vatRate ? (int)$vatRate : null;
    }

    /**
     * Получает ставку НДС для доставки из настроек конкретной доставки
     *
     * @param Order $order Заказ
     * @param Payment $payment Платеж
     * @return int|null Ставка НДС в процентах (0, 5, 10, 20) или null
     */
    protected function getDeliveryVatRate(Order $order, Payment $payment): ?int
    {
        $deliveryId = $order->getField('DELIVERY_ID');
        
        if ($deliveryId) {
            // Пытаемся получить ставку НДС из параметров платежной системы
            // Формат ключа: VAT_DELIVERY{ID_ДОСТАВКИ}
            try {
                $paySystem = $payment->getPaySystem();
                if ($paySystem) {
                    $psParams = $paySystem->getParamsBusValue($payment);
                    $vatKey = 'VAT_DELIVERY' . $deliveryId;
                    
                    if (isset($psParams[$vatKey]) && $psParams[$vatKey] !== null && $psParams[$vatKey] !== '') {
                        $vatRate = $psParams[$vatKey];
                        // Если значение в формате 0.05 (5%), конвертируем в проценты
                        if ($vatRate < 1) {
                            return (int)($vatRate * 100);
                        }
                        return (int)$vatRate;
                    }
                }
            } catch (\Exception $e) {
                // Игнорируем ошибки при получении параметров
            }
        }
        
        // Используем значение по умолчанию: 5%
        $vatRate = Option::get('level44.cloudkassir', 'delivery_vat_rate', '5');
        return $vatRate ? (int)$vatRate : null;
    }

    /**
     * Получает предмет расчета
     *
     * @return int
     */
    protected function getPaymentObject(): int
    {
        return (int)Option::get('level44.cloudkassir', 'payment_object', '1'); // 1 - товар
    }

    /**
     * Получает способ расчета
     *
     * @return int
     */
    protected function getPaymentMethod(): int
    {
        return (int)Option::get('level44.cloudkassir', 'payment_method', '1'); // 1 - предоплата 100%
    }

    /**
     * Получает ID бонусной платежной системы
     *
     * @return int ID платежной системы для бонусов
     */
    protected function getBonusPaymentSystemId(): int
    {
        return (int)Option::get('level44.cloudkassir', 'bonus_payment_system_id', '18');
    }

    /**
     * Получает сумму бонусного платежа из заказа
     *
     * @param Order $order Заказ
     * @return float Сумма бонусного платежа
     */
    protected function getBonusPaymentAmount(Order $order): float
    {
        $paymentCollection = $order->getPaymentCollection();
        $innerPaySystemId = $this->getBonusPaymentSystemId();
        $bonusAmount = 0.0;

        /** @var Payment $payment */
        foreach ($paymentCollection as $payment) {
            // Ищем только внутренние платежи (бонусы)
            if ($payment->getPaymentSystemId() == $innerPaySystemId && $payment->isPaid()) {
                $bonusAmount += (float)$payment->getSum();
            }
        }

        return $bonusAmount;
    }

    /**
     * Получает ИНН организации
     *
     * @return string
     */
    protected function getInn(): string
    {
        $inn = Option::get('level44.cloudkassir', 'inn', '');
        // Убираем пробелы и другие символы из ИНН
        return trim($inn);
    }

    /**
     * Получает систему налогообложения
     *
     * @return int
     */
    protected function getTaxationSystem(): int
    {
        return (int)Option::get('level44.cloudkassir', 'taxation_system', '1'); // 1 - УСН доход
    }

    /**
     * Форматирует номер телефона
     *
     * @param string $phone
     * @return string
     */
    protected function formatPhone(string $phone): string
    {
        // Удаляем все символы кроме цифр
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Если номер начинается с 8, заменяем на 7
        if (strlen($phone) === 11 && $phone[0] === '8') {
            $phone = '7' . substr($phone, 1);
        }

        // Если номер не начинается с 7, добавляем 7
        if (strlen($phone) === 10) {
            $phone = '7' . $phone;
        }

        return $phone;
    }

    /**
     * Отправляет POST запрос
     *
     * @param string $url
     * @param array $data
     * @return array
     */
    protected function post(string $url, array $data): array
    {
        try {
            $jsonData = Json::encode($data);
            $response = $this->httpClient->post($url, $jsonData);
            
            // Получаем HTTP код ответа
            $httpCode = $this->httpClient->getStatus();
            
            if ($response === false) {
                $error = $this->httpClient->getError();
                return [
                    'Success' => false,
                    'error' => "HTTP {$httpCode} - " . ($error ?: 'Ошибка HTTP запроса'),
                ];
            }
            
            if ($httpCode === 404) {
                return [
                    'Success' => false,
                    'error' => "404 - not found",
                ];
            }

            $responseData = Json::decode($response);

            return is_array($responseData) ? $responseData : ['Success' => false, 'error' => 'Неверный формат ответа'];

        } catch (\Exception $e) {
            return [
                'Success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Логирование событий CloudKassir
     *
     * @param string $message Сообщение для лога
     * @param string $level Уровень логирования
     * @return void
     */
    protected function log(string $message, string $level = 'INFO'): void
    {
        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/cloudkassir.log';
        $date = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$date}] [{$level}] {$message}\n", FILE_APPEND);
    }
}

