# Интеграция с OSMI Card

Модуль для интеграции сайта на Bitrix с системой лояльности OSMI Card.

## Описание

При регистрации нового пользователя на сайте автоматически создается карта лояльности в системе OSMI Card. Номер карты и ID карты сохраняются в профиле пользователя.

## Установка

### 1. Файлы уже установлены

Файлы интеграции находятся в:
- `/local/php_interface/lib/Level44/OsmiCard/` - классы для работы с API
- `/local/php_interface/lib/Level44/Event/OsmiCardHandlers.php` - обработчики событий
- `/bitrix/admin/osmi_card_settings.php` - страница настроек в админке

### 2. Создание пользовательских полей

Перейдите в административную панель Bitrix:
- **Настройки** → **OSMI Card**
- Нажмите кнопку **"Создать поля"**

Это создаст два пользовательских поля:
- `UF_OSMI_CARD_NUMBER` - номер карты лояльности
- `UF_OSMI_CARD_ID` - ID карты в системе OSMI

### 3. Настройка параметров

В разделе **Настройки** → **OSMI Card** заполните:

- **Включить интеграцию** - установите галочку для активации
- **URL API** - обычно `https://api.osmicards.com/v1`
- **API ключ** ⚠️ - получите в личном кабинете OSMI Card
- **Секретный ключ** - если требуется для вашего аккаунта
- **ID проекта** ⚠️ - ваш ID проекта в системе OSMI Card

⚠️ - обязательные поля

### 4. Тестирование

После настройки параметров:
1. Введите номер телефона существующей карты в поле **"Номер телефона для теста"**
2. Нажмите **"Проверить подключение"**
3. Если все настроено правильно, отобразится информация о карте

## Использование

### Автоматическая регистрация карты

При регистрации нового пользователя на сайте:
1. Срабатывает событие `OnAfterUserRegister`
2. Создается запрос к API OSMI Card для регистрации новой карты
3. Номер карты сохраняется в поле `UF_OSMI_CARD_NUMBER` профиля пользователя
4. ID карты сохраняется в поле `UF_OSMI_CARD_ID`

### Получение номера карты пользователя

```php
use Level44\Event\OsmiCardHandlers;

$userId = 123; // ID пользователя
$cardNumber = OsmiCardHandlers::getUserCardNumber($userId);

if ($cardNumber) {
    echo "Номер карты: " . $cardNumber;
}
```

### Работа с API напрямую

```php
use Level44\OsmiCard\Api;

$api = new Api();

// Получить информацию о карте по телефону
$result = $api->getCardByPhone('79001234567');
if ($result['success']) {
    $cardData = $result['data'];
    // ...
}

// Начислить бонусы
$result = $api->addBonuses('1234567890', 100, 'Покупка на сайте');
if ($result['success']) {
    echo "Бонусы начислены";
}

// Списать бонусы
$result = $api->deductBonuses('1234567890', 50, 'Оплата бонусами');
if ($result['success']) {
    echo "Бонусы списаны";
}

// Получить баланс карты
$result = $api->getCardBalance('1234567890');
if ($result['success']) {
    $balance = $result['data']['balance'];
    echo "Баланс: " . $balance;
}
```

## API методы

### OsmiCard\Api

#### `registerCard(array $userData): array`
Регистрация новой карты лояльности.

**Параметры:**
- `phone` (string, обязательно) - номер телефона
- `email` (string) - email
- `firstName` (string) - имя
- `lastName` (string) - фамилия
- `secondName` (string) - отчество
- `birthDate` (string) - дата рождения
- `gender` (string) - пол

**Возвращает:**
```php
[
    'success' => true,
    'data' => [
        'cardNumber' => '1234567890',
        'id' => 'card_id_123',
        // другие поля
    ]
]
```

#### `getCardByPhone(string $phone): array`
Получение информации о карте по номеру телефона.

#### `getCardBalance(string $cardNumber): array`
Получение баланса карты.

#### `addBonuses(string $cardNumber, float $amount, string $description): array`
Начисление бонусов на карту.

#### `deductBonuses(string $cardNumber, float $amount, string $description): array`
Списание бонусов с карты.

## Логирование

Все события регистрации карт логируются в файл:
```
/upload/osmi_card.log
```

Формат лога:
```
[2025-10-09 12:34:56] Успешно создана карта лояльности для пользователя ID: 123, номер карты: 1234567890
[2025-10-09 12:35:01] Ошибка создания карты для пользователя ID: 124. Ошибка: Invalid phone number
```

## Обработчики событий

### OnAfterUserRegister
Срабатывает после регистрации нового пользователя. Автоматически создает карту лояльности в OSMI Card.

### OnAfterUserUpdate
Зарезервирован для обновления данных карты при изменении профиля пользователя.

## Настройки

Все настройки хранятся в опциях модуля `level44.osmicard`:
- `enabled` - включена ли интеграция (Y/N)
- `api_url` - URL API
- `api_key` - API ключ
- `secret_key` - секретный ключ
- `project_id` - ID проекта

Получить настройку:
```php
use Level44\OsmiCard\Settings;

$apiKey = Settings::get('api_key');
$isEnabled = Settings::isValid(); // проверка корректности настроек
```

## Требования

- PHP 7.4+
- Bitrix Framework
- Модуль Main
- Доступ к API OSMI Card

## Документация OSMI Card API

Полная документация API: https://apidocs.osmicards.com/

## Поддержка

Все вопросы по интеграции направляйте разработчику проекта.

## Структура файлов

```
/local/php_interface/lib/Level44/
├── Event/
│   └── OsmiCardHandlers.php     # Обработчики событий
└── OsmiCard/
    ├── Api.php                  # Класс для работы с API
    ├── Installer.php            # Установка пользовательских полей
    ├── Settings.php             # Работа с настройками
    └── README.md               # Эта документация

/bitrix/admin/
├── osmi_card_settings.php       # Страница настроек
└── osmi_card_menu.php          # Пункт меню в админке

/upload/
└── osmi_card.log               # Лог файл
```

## Лицензия

Proprietary

