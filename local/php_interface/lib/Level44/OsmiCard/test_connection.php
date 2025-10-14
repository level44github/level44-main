<?php
/**
 * Скрипт тестирования подключения к OSMI Card API
 * Использование: откройте в браузере /local/php_interface/lib/Level44/OsmiCard/test_connection.php
 */

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Level44\OsmiCard\Api;
use Level44\OsmiCard\Settings;

// Проверка прав доступа
global $USER;
if (!$USER->IsAdmin()) {
    die("Доступ запрещен. Требуются права администратора.");
}

echo "<h1>Тестирование подключения к OSMI Card API</h1>";
echo "<hr>";

// Получаем настройки
$settings = Settings::getAll();

echo "<h2>1. Проверка настроек</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Параметр</th><th>Значение</th><th>Статус</th></tr>";

$checks = [
    'Включено' => [$settings['enabled'], $settings['enabled'] === 'Y'],
    'API URL' => [$settings['api_url'], !empty($settings['api_url'])],
    'Username' => [!empty($settings['api_username']) ? 'Указан' : 'Не указан', !empty($settings['api_username'])],
    'Password' => [!empty($settings['api_password']) ? 'Указан' : 'Не указан', !empty($settings['api_password'])],
    'Template ID' => [$settings['template_id'], !empty($settings['template_id'])],
];

foreach ($checks as $name => $check) {
    $status = $check[1] ? '✅' : '❌';
    $style = $check[1] ? 'color: green;' : 'color: red;';
    echo "<tr>";
    echo "<td><strong>{$name}</strong></td>";
    echo "<td>" . htmlspecialchars($check[0]) . "</td>";
    echo "<td style='{$style}'>{$status}</td>";
    echo "</tr>";
}

echo "</table>";

if (!Settings::isValid()) {
    echo "<p style='color: red;'><strong>⚠️ Настройки некорректны! Заполните все обязательные поля.</strong></p>";
    echo "<p><a href='/bitrix/admin/osmi_card_settings.php'>← Перейти к настройкам</a></p>";
    die();
}

echo "<p style='color: green;'>✅ Все обязательные настройки заполнены</p>";

// Тест подключения
echo "<h2>2. Тест подключения к API</h2>";

$api = new Api();

echo "<h3>2.1. Получение списка шаблонов (GET /templates)</h3>";

try {
    $result = $api->getTemplates();
    
    if ($result['success']) {
        echo "<p style='color: green;'>✅ Успешно получен список шаблонов</p>";
        echo "<pre>" . htmlspecialchars(print_r($result['data'], true)) . "</pre>";
    } else {
        echo "<p style='color: red;'>❌ Ошибка: " . htmlspecialchars($result['error']) . "</p>";
    }
} catch (\Exception $e) {
    echo "<p style='color: red;'>❌ Исключение: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h3>2.2. Получение списка карт (GET /passes)</h3>";

try {
    $result = $api->getPasses(['limit' => 5]);
    
    if ($result['success']) {
        echo "<p style='color: green;'>✅ Успешно получен список карт</p>";
        echo "<pre>" . htmlspecialchars(print_r($result['data'], true)) . "</pre>";
    } else {
        echo "<p style='color: red;'>❌ Ошибка: " . htmlspecialchars($result['error']) . "</p>";
    }
} catch (\Exception $e) {
    echo "<p style='color: red;'>❌ Исключение: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Проверка лог файла
echo "<h2>3. Лог файлы</h2>";

$apiLogFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/osmi_card_api.log';
$mainLogFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/osmi_card.log';

echo "<h3>3.1. API лог (последние 50 строк)</h3>";
if (file_exists($apiLogFile)) {
    $lines = file($apiLogFile);
    $lastLines = array_slice($lines, -50);
    echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 400px; overflow: auto;'>";
    echo htmlspecialchars(implode('', $lastLines));
    echo "</pre>";
    echo "<p><strong>Полный лог:</strong> /upload/osmi_card_api.log</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Файл лога не найден</p>";
}

echo "<h3>3.2. Основной лог (последние 20 строк)</h3>";
if (file_exists($mainLogFile)) {
    $lines = file($mainLogFile);
    $lastLines = array_slice($lines, -20);
    echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 300px; overflow: auto;'>";
    echo htmlspecialchars(implode('', $lastLines));
    echo "</pre>";
    echo "<p><strong>Полный лог:</strong> /upload/osmi_card.log</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Файл лога не найден</p>";
}

// Тест HTTP Digest вручную
echo "<h2>4. Тест HTTP Digest вручную</h2>";

echo "<p>Проверка HTTP Digest авторизации с помощью cURL:</p>";

$testUrl = $settings['api_url'] . '/templates';
$username = $settings['api_username'];
$password = $settings['api_password'];

if (!empty($username) && !empty($password)) {
    $ch = curl_init($testUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
    curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p><strong>URL:</strong> " . htmlspecialchars($testUrl) . "</p>";
    echo "<p><strong>HTTP Code:</strong> {$httpCode}</p>";
    
    if ($httpCode === 200) {
        echo "<p style='color: green;'>✅ HTTP Digest авторизация работает!</p>";
    } else {
        echo "<p style='color: red;'>❌ HTTP Digest авторизация не работает</p>";
    }
    
    echo "<h4>Ответ сервера:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 400px; overflow: auto;'>";
    echo htmlspecialchars($response);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ Username или Password не указаны</p>";
}

// Рекомендации
echo "<h2>5. Рекомендации</h2>";

echo "<ul>";
echo "<li>Проверьте правильность <strong>Username</strong> и <strong>Password</strong> в настройках</li>";
echo "<li>Убедитесь, что URL = <strong>https://api.osmicards.com/v2</strong></li>";
echo "<li>Проверьте лог файлы для детальной информации об ошибках</li>";
echo "<li>Если API возвращает HTML вместо JSON - проблема в авторизации</li>";
echo "<li>Убедитесь, что у вашего аккаунта есть доступ к API</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='/bitrix/admin/osmi_card_settings.php'>← Вернуться к настройкам</a></p>";
?>

