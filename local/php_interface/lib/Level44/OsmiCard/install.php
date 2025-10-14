<?php
/**
 * Скрипт для быстрой установки интеграции OSMI Card
 * 
 * Запустите этот файл один раз для создания необходимых полей
 * Использование: откройте в браузере /local/php_interface/lib/Level44/OsmiCard/install.php
 */

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Level44\OsmiCard\Installer;

// Проверка прав доступа
global $USER;
if (!$USER->IsAdmin()) {
    die("Доступ запрещен. Требуются права администратора.");
}

echo "<h1>Установка интеграции OSMI Card</h1>";
echo "<hr>";

// Установка полей
echo "<h2>Создание пользовательских полей...</h2>";

$results = Installer::install();

if ($results['userField']) {
    echo "<p style='color: green;'>✓ Поле UF_OSMI_CARD_NUMBER создано успешно</p>";
} else {
    echo "<p style='color: orange;'>⚠ Поле UF_OSMI_CARD_NUMBER уже существует</p>";
}

if ($results['cardIdField']) {
    echo "<p style='color: green;'>✓ Поле UF_OSMI_CARD_ID создано успешно</p>";
} else {
    echo "<p style='color: orange;'>⚠ Поле UF_OSMI_CARD_ID уже существует</p>";
}

echo "<hr>";
echo "<h2>Установка завершена!</h2>";
echo "<p>Следующие шаги:</p>";
echo "<ol>";
echo "<li>Перейдите в админку: <a href='/bitrix/admin/osmi_card_settings.php'>Настройки OSMI Card</a></li>";
echo "<li>Заполните API ключ и ID проекта</li>";
echo "<li>Включите интеграцию</li>";
echo "<li>Протестируйте подключение</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='/bitrix/admin/'>← Вернуться в админку</a></p>";

