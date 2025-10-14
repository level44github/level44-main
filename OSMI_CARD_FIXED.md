# OSMI Card Integration - ИСПРАВЛЕНО ✅

## ⚠️ Что было исправлено

Интеграция была обновлена согласно **реальной документации OSMI Card API**.

### Проблема
Первоначально использовались несуществующие API endpoints:
- ❌ `/cards/register` 
- ❌ `/cards/find`
- ❌ `/cards/{id}/balance`
- ❌ `/cards/{id}/bonuses/add`

### Решение
Обновлены на реальные endpoints из документации:
- ✅ `POST /v2t/passes` - создание карты
- ✅ `GET /v2t/passes` - получение списка карт
- ✅ `GET /v2t/passes/{id}` - получение карты по ID
- ✅ `PUT /v2t/passes/{id}` - обновление карты
- ✅ `DELETE /v2t/passes/{id}` - удаление карты
- ✅ `POST /v2t/passes/{id}/push` - отправка push-уведомления
- ✅ `GET /v2t/templates` - получение списка шаблонов

## 📝 Измененные файлы

### 1. `/local/php_interface/lib/Level44/OsmiCard/Api.php`
**Изменения:**
- ✅ Изменен URL API на `https://api.osmicards.com/v2t`
- ✅ Добавлено свойство `$templateId` (обязательный параметр)
- ✅ Метод `registerCard()` теперь использует `POST /passes`
- ✅ Структура запроса изменена согласно документации:
  ```php
  [
    'template_id' => 'ID шаблона',
    'serial_number' => 'ID пользователя', 
    'barcode' => [...],
    'fields' => [...]
  ]
  ```
- ✅ Удалены несуществующие методы `getCardByPhone()`, `getCardBalance()`, `addBonuses()`, `deductBonuses()`
- ✅ Добавлены реальные методы:
  - `getPass($passId)` - получение карты по ID
  - `getPasses($filters)` - получение списка карт
  - `updatePass($passId, $data)` - обновление карты
  - `deletePass($passId)` - удаление карты
  - `sendPushNotification($passId, $message)` - отправка уведомления
  - `getTemplates()` - получение шаблонов

### 2. `/local/php_interface/lib/Level44/Event/OsmiCardHandlers.php`
**Изменения:**
- ✅ Удалена проверка обязательности телефона (теперь опциональное поле)
- ✅ Добавлена передача `userId` в данные пользователя
- ✅ Изменен парсинг ответа: `serial_number` вместо `cardNumber`
- ✅ Добавлено логирование `pass_url`

### 3. `/local/php_interface/lib/Level44/OsmiCard/Settings.php`
**Изменения:**
- ✅ Изменен URL API по умолчанию на `https://api.osmicards.com/v2t`
- ✅ Добавлена настройка `template_id`
- ✅ В `isValid()` проверка изменена: `template_id` вместо `project_id`

### 4. `/bitrix/admin/osmi_card_settings.php`
**Изменения:**
- ✅ Добавлено поле **"ID шаблона"** (обязательное)
- ✅ Добавлена кнопка **"Получить список шаблонов"**
- ✅ Изменен метод тестирования: теперь используется `getPasses()`
- ✅ Обновлена информационная секция
- ✅ `project_id` теперь опциональный

### 5. Документация
**Создан новый файл:** `/local/php_interface/lib/Level44/OsmiCard/README_UPDATED.md`
- ✅ Полное описание с правильными методами API
- ✅ Примеры использования
- ✅ Таблица сравнения старой и новой версии

## 🚀 Как использовать исправленную версию

### Шаг 1: Настройка

1. Админка → **Настройки** → **OSMI Card**
2. Заполните:
   - ✅ **API ключ** (получите в личном кабинете OSMI Card)
   - ✅ **Template ID** (см. шаг 2)
3. Сохраните

### Шаг 2: Получение Template ID

1. Убедитесь, что API ключ указан
2. Нажмите **"Получить список шаблонов"**
3. Из списка скопируйте ID нужного шаблона
4. Вставьте в поле **"ID шаблона"**
5. Сохраните

### Шаг 3: Тестирование

1. Нажмите **"Проверить подключение"**
2. Если все ОК - увидите сообщение об успехе

### Шаг 4: Проверка

1. Зарегистрируйте нового пользователя
2. Проверьте `/upload/osmi_card.log`
3. Должна быть запись:
   ```
   [2025-10-09 12:34:56] Успешно создана карта лояльности для пользователя ID: 123, serial_number: 123, URL: https://...
   ```

## 📋 Новая структура данных

### При создании карты отправляется:

```json
{
  "template_id": "ваш_template_id_из_настроек",
  "serial_number": "123",
  "barcode": {
    "message": "123",
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

### В ответ приходит:

```json
{
  "id": "pass_id_abc123",
  "serial_number": "123",
  "pass_url": "https://api.osmicards.com/passes/...",
  "template_id": "template_id_xyz",
  ...
}
```

### Что сохраняется в профиле:

- `UF_OSMI_CARD_NUMBER` = `serial_number` (ID пользователя)
- `UF_OSMI_CARD_ID` = `id` (ID карты в OSMI)

## 🔧 Обязательные настройки

| Параметр | Было | Стало |
|----------|------|-------|
| API URL | `https://api.osmicards.com/v1` | `https://api.osmicards.com/v2t` |
| API ключ | ✅ Обязательно | ✅ Обязательно |
| Project ID | ✅ Обязательно | ⚪ Опционально |
| **Template ID** | ❌ Не было | ✅ **Обязательно** |

## 📚 Примеры использования

### Получить serial_number карты пользователя:

```php
use Level44\Event\OsmiCardHandlers;

$userId = $USER->GetID();
$serialNumber = OsmiCardHandlers::getUserCardNumber($userId);
// Вернет ID пользователя, который используется как serial_number
```

### Создать карту вручную:

```php
use Level44\OsmiCard\Api;

$api = new Api();
$result = $api->registerCard([
    'userId' => 123,
    'phone' => '79001234567',
    'email' => 'user@example.com',
    'firstName' => 'Иван',
    'lastName' => 'Иванов',
]);

if ($result['success']) {
    echo "Serial number: " . $result['data']['serial_number'];
    echo "Pass ID: " . $result['data']['id'];
    echo "URL карты: " . $result['data']['pass_url'];
}
```

### Обновить данные карты:

```php
$api = new Api();
$result = $api->updatePass('pass_id_123', [
    'fields' => [
        'balance' => 500,
        'status' => 'gold'
    ]
]);
```

### Отправить push-уведомление:

```php
$api = new Api();
$result = $api->sendPushNotification(
    'pass_id_123', 
    'Вам начислено 100 бонусов!'
);
```

### Получить список шаблонов:

```php
$api = new Api();
$result = $api->getTemplates();

if ($result['success']) {
    foreach ($result['data'] as $template) {
        echo "ID: {$template['id']}\n";
        echo "Название: {$template['name']}\n";
    }
}
```

## ⚡ Что происходит при регистрации

1. Пользователь регистрируется на сайте
2. Срабатывает `OnAfterUserRegister`
3. Отправляется запрос `POST /v2t/passes` с:
   - `template_id` из настроек
   - `serial_number` = ID пользователя
   - `fields` с данными пользователя
4. В ответ приходит:
   - `id` - ID карты в OSMI
   - `serial_number` - серийный номер (= ID пользователя)
   - `pass_url` - URL для скачивания карты
5. Данные сохраняются в профиль пользователя
6. Все логируется в `/upload/osmi_card.log`

## 📄 Документация

- **Обновленная документация**: `/local/php_interface/lib/Level44/OsmiCard/README_UPDATED.md`
- **Официальная документация API**: https://apidocs.osmicards.com/
- **Postman Collection**: https://www.postman.com/osmicards/

## ✅ Чек-лист проверки

После исправлений убедитесь:

- [x] API URL изменен на `https://api.osmicards.com/v2t`
- [x] Указан API ключ
- [x] Указан Template ID (получен через "Получить список шаблонов")
- [x] Интеграция включена
- [x] Пользовательские поля созданы
- [x] Тест подключения проходит успешно
- [x] При регистрации пользователя создается карта
- [x] Лог `/upload/osmi_card.log` содержит успешные записи

## 🐛 Решение проблем

### "Не указан template_id"
**Решение:** Укажите Template ID в настройках. Получите его кнопкой "Получить список шаблонов".

### "Invalid API key"
**Решение:** Проверьте правильность API ключа в личном кабинете OSMI Card.

### "Template not found"
**Решение:** Убедитесь, что Template ID существует. Проверьте через "Получить список шаблонов".

### Карта не создается
**Решение:** 
1. Проверьте лог `/upload/osmi_card.log`
2. Убедитесь, что интеграция включена
3. Проверьте корректность настроек

## 📞 Поддержка

- **Лог файл**: `/upload/osmi_card.log`
- **Документация OSMI**: https://apidocs.osmicards.com/
- **Обновленный README**: `/local/php_interface/lib/Level44/OsmiCard/README_UPDATED.md`

---

**Статус**: ✅ Исправлено и готово к использованию  
**Версия**: 1.1.0 (исправленная)  
**Дата исправления**: 2025-10-09

**Все изменения внесены согласно официальной документации OSMI Card API!** 🎉

