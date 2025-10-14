# Интеграция с OSMI Card (ОБНОВЛЕНО)

## ⚠️ ВАЖНО: Исправленная версия

Эта версия использует реальные API методы OSMI Card согласно документации.

## Описание

При регистрации нового пользователя на сайте автоматически создается карта (pass) в системе OSMI Card через endpoint `POST /v2t/passes`.

## Что изменилось

### ❌ Старые (неправильные) методы:
- `/cards/register` - не существует
- `/cards/find` - не существует  
- `/cards/{id}/balance` - не существует
- `/cards/{id}/bonuses/add` - не существует

### ✅ Новые (правильные) методы:
- `POST /passes` - создание карты (pass)
- `GET /passes` - получение списка карт
- `GET /passes/{id}` - получение карты по ID
- `PUT /passes/{id}` - обновление карты
- `DELETE /passes/{id}` - удаление карты
- `POST /passes/{id}/push` - отправка push-уведомления
- `GET /templates` - получение списка шаблонов

## Установка

### 1. Создание пользовательских полей

Перейдите в административную панель Bitrix:
- **Настройки** → **OSMI Card**
- Нажмите кнопку **"Создать поля"**

Будут созданы поля:
- `UF_OSMI_CARD_NUMBER` - serial_number карты
- `UF_OSMI_CARD_ID` - ID карты в системе OSMI

### 2. Настройка параметров

В разделе **Настройки** → **OSMI Card** заполните:

**Обязательные поля:**
- ✅ **Включить интеграцию** - установите галочку
- ✅ **API ключ** - получите в личном кабинете OSMI Card
- ✅ **Template ID** - ID шаблона для создания карт

**Опциональные поля:**
- **URL API** - по умолчанию `https://api.osmicards.com/v2t`
- **Секретный ключ** - если требуется
- **ID проекта** - если требуется

### 3. Получение Template ID

1. Укажите API ключ
2. Нажмите **"Получить список шаблонов"**
3. Скопируйте ID нужного шаблона
4. Вставьте в поле **"ID шаблона"**
5. Сохраните

### 4. Тестирование

Нажмите **"Проверить подключение"** для проверки корректности настроек.

## Использование

### Автоматическая регистрация карты

При регистрации нового пользователя:
1. Срабатывает событие `OnAfterUserRegister`
2. Создается запрос `POST /passes` с данными:
   ```json
   {
     "template_id": "ваш_template_id",
     "serial_number": "ID_пользователя",
     "barcode": {
       "message": "ID_пользователя",
       "format": "QR"
     },
     "fields": {
       "phone": "79001234567",
       "email": "user@example.com",
       "first_name": "Иван",
       "last_name": "Иванов",
       "middle_name": "Иванович"
     }
   }
   ```
3. Serial number (ID пользователя) сохраняется в `UF_OSMI_CARD_NUMBER`
4. ID карты сохраняется в `UF_OSMI_CARD_ID`

### Получение номера карты пользователя

```php
use Level44\Event\OsmiCardHandlers;

$userId = 123;
$serialNumber = OsmiCardHandlers::getUserCardNumber($userId);

if ($serialNumber) {
    echo "Serial number карты: " . $serialNumber;
}
```

### Работа с API напрямую

```php
use Level44\OsmiCard\Api;

$api = new Api();

// Создать карту (pass)
$result = $api->registerCard([
    'userId' => 123,
    'phone' => '79001234567',
    'email' => 'user@example.com',
    'firstName' => 'Иван',
    'lastName' => 'Иванов',
]);

if ($result['success']) {
    echo "Pass создан: " . $result['data']['serial_number'];
    echo "URL карты: " . $result['data']['pass_url'];
}

// Получить список карт (passes)
$result = $api->getPasses(['limit' => 10]);

// Получить конкретную карту по ID
$result = $api->getPass('pass_id_123');

// Обновить карту
$result = $api->updatePass('pass_id_123', [
    'fields' => [
        'balance' => 100
    ]
]);

// Отправить push-уведомление
$result = $api->sendPushNotification('pass_id_123', 'Ваш баланс: 100 бонусов');

// Удалить карту
$result = $api->deletePass('pass_id_123');

// Получить список шаблонов
$result = $api->getTemplates();
if ($result['success']) {
    foreach ($result['data'] as $template) {
        echo "ID: {$template['id']}, Название: {$template['name']}\n";
    }
}
```

## API методы

### `registerCard(array $userData): array`

Создание новой карты через `POST /passes`.

**Параметры:**
- `userId` (int) - ID пользователя (используется как serial_number)
- `phone` (string) - телефон
- `email` (string) - email
- `firstName` (string) - имя
- `lastName` (string) - фамилия
- `secondName` (string) - отчество

**Возвращает:**
```php
[
    'success' => true,
    'data' => [
        'id' => 'pass_id',
        'serial_number' => '123',
        'pass_url' => 'https://...',
        'full_response' => [...]
    ]
]
```

### `getPass(string $passId): array`

Получение карты по ID через `GET /passes/{id}`.

### `getPasses(array $filters = []): array`

Получение списка карт через `GET /passes`.

Фильтры: `serial_number`, `template_id`, `limit`, и т.д.

### `updatePass(string $passId, array $data): array`

Обновление карты через `PUT /passes/{id}`.

### `deletePass(string $passId): array`

Удаление карты через `DELETE /passes/{id}`.

### `sendPushNotification(string $passId, string $message): array`

Отправка push-уведомления на карту через `POST /passes/{id}/push`.

### `getTemplates(): array`

Получение списка доступных шаблонов через `GET /templates`.

## Структура ответа API

### Успешный ответ:
```php
[
    'success' => true,
    'data' => [/* данные */]
]
```

### Ошибка:
```php
[
    'success' => false,
    'error' => 'Описание ошибки',
    'code' => 400
]
```

## Логирование

Все события логируются в `/upload/osmi_card.log`:

```
[2025-10-09 12:34:56] Успешно создана карта лояльности для пользователя ID: 123, serial_number: 123, URL: https://...
[2025-10-09 12:35:01] Ошибка создания карты для пользователя ID: 124. Ошибка: Не указан template_id
```

## Проверка работы

1. Зарегистрируйте нового пользователя
2. Проверьте лог `/upload/osmi_card.log`
3. В профиле пользователя должно быть поле **"Номер карты OSMI"** со значением = ID пользователя

## Настройки

Хранятся в опциях модуля `level44.osmicard`:

| Параметр | Описание | Обязательно |
|----------|----------|-------------|
| enabled | Включена ли интеграция (Y/N) | Да |
| api_url | URL API | Нет (по умолчанию: https://api.osmicards.com/v2t) |
| api_key | API ключ | Да |
| secret_key | Секретный ключ | Нет |
| project_id | ID проекта | Нет |
| **template_id** | **ID шаблона** | **Да** |

## Отличия от старой версии

| Старая версия | Новая версия |
|---------------|--------------|
| `/cards/register` | `POST /passes` |
| `cardNumber` | `serial_number` |
| `project_id` обязателен | `template_id` обязателен |
| API v1 | API v2t |

## Документация

- **API OSMI Card**: https://apidocs.osmicards.com/
- **Postman Collection**: https://www.postman.com/osmicards/

## Поддержка

При возникновении проблем проверьте:
1. ✅ Корректность API ключа
2. ✅ Указан ли Template ID
3. ✅ Включена ли интеграция
4. ✅ Создались ли пользовательские поля
5. 📝 Наличие ошибок в логе `/upload/osmi_card.log`

---

**Версия**: 1.1.0 (исправленная)  
**Дата**: 2025-10-09

