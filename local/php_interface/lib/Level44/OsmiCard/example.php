<?php
/**
 * Примеры использования OSMI Card API
 */

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Level44\OsmiCard\Api;
use Level44\OsmiCard\Settings;
use Level44\Event\OsmiCardHandlers;

// Проверяем, включена ли интеграция
if (!Settings::isValid()) {
    die("Интеграция OSMI Card не настроена. Перейдите в настройки.");
}

$api = new Api();

// ============================================
// Пример 1: Регистрация новой карты
// ============================================

echo "<h2>Пример 1: Регистрация новой карты</h2>";

$userData = [
    'phone' => '79001234567',
    'email' => 'test@example.com',
    'firstName' => 'Иван',
    'lastName' => 'Иванов',
    'secondName' => 'Иванович',
];

$result = $api->registerCard($userData);

if ($result['success']) {
    echo "✓ Карта зарегистрирована успешно<br>";
    echo "Номер карты: " . $result['data']['cardNumber'] . "<br>";
    echo "<pre>" . print_r($result['data'], true) . "</pre>";
} else {
    echo "✗ Ошибка: " . $result['error'] . "<br>";
}

echo "<hr>";

// ============================================
// Пример 2: Получение информации о карте
// ============================================

echo "<h2>Пример 2: Получение информации о карте</h2>";

$phone = '79001234567';
$result = $api->getCardByPhone($phone);

if ($result['success']) {
    echo "✓ Информация о карте получена<br>";
    echo "<pre>" . print_r($result['data'], true) . "</pre>";
} else {
    echo "✗ Ошибка: " . $result['error'] . "<br>";
}

echo "<hr>";

// ============================================
// Пример 3: Получение баланса карты
// ============================================

echo "<h2>Пример 3: Получение баланса карты</h2>";

$cardNumber = '1234567890'; // Замените на реальный номер карты

$result = $api->getCardBalance($cardNumber);

if ($result['success']) {
    echo "✓ Баланс получен<br>";
    echo "Баланс: " . $result['data']['balance'] . " бонусов<br>";
    echo "<pre>" . print_r($result['data'], true) . "</pre>";
} else {
    echo "✗ Ошибка: " . $result['error'] . "<br>";
}

echo "<hr>";

// ============================================
// Пример 4: Начисление бонусов
// ============================================

echo "<h2>Пример 4: Начисление бонусов</h2>";

$cardNumber = '1234567890'; // Замените на реальный номер карты
$amount = 100;
$description = 'Бонус за покупку на сайте';

$result = $api->addBonuses($cardNumber, $amount, $description);

if ($result['success']) {
    echo "✓ Бонусы начислены успешно<br>";
    echo "Начислено: {$amount} бонусов<br>";
    echo "<pre>" . print_r($result['data'], true) . "</pre>";
} else {
    echo "✗ Ошибка: " . $result['error'] . "<br>";
}

echo "<hr>";

// ============================================
// Пример 5: Списание бонусов
// ============================================

echo "<h2>Пример 5: Списание бонусов</h2>";

$cardNumber = '1234567890'; // Замените на реальный номер карты
$amount = 50;
$description = 'Оплата заказа бонусами';

$result = $api->deductBonuses($cardNumber, $amount, $description);

if ($result['success']) {
    echo "✓ Бонусы списаны успешно<br>";
    echo "Списано: {$amount} бонусов<br>";
    echo "<pre>" . print_r($result['data'], true) . "</pre>";
} else {
    echo "✗ Ошибка: " . $result['error'] . "<br>";
}

echo "<hr>";

// ============================================
// Пример 6: Получение номера карты пользователя
// ============================================

echo "<h2>Пример 6: Получение номера карты пользователя</h2>";

global $USER;
if ($USER->IsAuthorized()) {
    $userId = $USER->GetID();
    $cardNumber = OsmiCardHandlers::getUserCardNumber($userId);
    
    if ($cardNumber) {
        echo "✓ Номер карты текущего пользователя: {$cardNumber}<br>";
    } else {
        echo "⚠ У текущего пользователя нет карты лояльности<br>";
    }
} else {
    echo "⚠ Пользователь не авторизован<br>";
}

echo "<hr>";

// ============================================
// Пример 7: Интеграция с заказом
// ============================================

echo "<h2>Пример 7: Начисление бонусов за заказ</h2>";
echo "<p>Пример обработчика события при оплате заказа:</p>";

echo "<pre>";
echo htmlspecialchars('
// В вашем обработчике события OnSaleOrderPaid
function OnOrderPaidHandler($orderId, $value) {
    if ($value !== "Y") {
        return;
    }
    
    use Bitrix\Sale\Order;
    use Level44\OsmiCard\Api;
    use Level44\Event\OsmiCardHandlers;
    
    $order = Order::load($orderId);
    $userId = $order->getUserId();
    
    // Получаем номер карты пользователя
    $cardNumber = OsmiCardHandlers::getUserCardNumber($userId);
    
    if (!$cardNumber) {
        return; // У пользователя нет карты
    }
    
    // Начисляем 5% от суммы заказа
    $orderSum = $order->getPrice();
    $bonusAmount = $orderSum * 0.05;
    
    $api = new Api();
    $result = $api->addBonuses(
        $cardNumber, 
        $bonusAmount, 
        "Бонус за заказ #{$orderId}"
    );
    
    if ($result["success"]) {
        // Бонусы начислены
    }
}

// Регистрация обработчика
AddEventHandler("sale", "OnSaleOrderPaid", "OnOrderPaidHandler");
');
echo "</pre>";

echo "<hr>";
echo "<p><a href='/bitrix/admin/osmi_card_settings.php'>← Вернуться к настройкам</a></p>";

