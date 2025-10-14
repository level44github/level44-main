# OSMI Card - HTTP Digest Authentication

## 🔐 Изменение авторизации

Интеграция обновлена для использования **HTTP Digest Authentication** вместо Bearer Token.

## Что изменилось

### ❌ Было (неправильно):
- **Авторизация**: Bearer Token
- **Поля**: `api_key`, `secret_key`
- **URL**: `https://api.osmicards.com/v2t`
- **Заголовок**: `Authorization: Bearer {token}`

### ✅ Стало (правильно):
- **Авторизация**: HTTP Digest Authentication
- **Поля**: `api_username`, `api_password`
- **URL**: `https://api.osmicards.com/v2`
- **Метод**: HTTP Digest (встроенный в HttpClient)

## Технические детали

### HTTP Digest Authentication

HTTP Digest - это метод аутентификации, который:
- Не передает пароль в открытом виде
- Использует хэширование MD5
- Требует username и password
- Более безопасен чем Basic Auth

### Реализация в коде

```php
// В Api.php
$this->httpClient = new HttpClient([
    'socketTimeout' => 30,
    'streamTimeout' => 30,
]);

// HTTP Digest авторизация
if (!empty($this->username) && !empty($this->password)) {
    $this->httpClient->setAuthorization($this->username, $this->password);
}
```

Метод `setAuthorization()` из Bitrix `HttpClient` автоматически обрабатывает HTTP Digest.

## Настройка

### 1. Получение credentials

Получите Username и Password в личном кабинете OSMI Card:
- Войдите в личный кабинет
- Перейдите в раздел API
- Скопируйте Username и Password

### 2. Настройка в админке

1. **Настройки** → **OSMI Card**
2. Заполните обязательные поля:
   - ✅ **Username (HTTP Digest)** - имя пользователя
   - ✅ **Password (HTTP Digest)** - пароль
   - ✅ **Template ID** - ID шаблона карты
3. Сохраните

### 3. URL API

По умолчанию используется:
```
https://api.osmicards.com/v2
```

Изменилось с `/v2t` на `/v2`.

## Endpoints

Все endpoints остались прежними, изменился только базовый URL:

| Endpoint | URL |
|----------|-----|
| Создание карты | `POST /v2/passes` |
| Получение карты | `GET /v2/passes/{id}` |
| Список карт | `GET /v2/passes` |
| Обновление карты | `PUT /v2/passes/{id}` |
| Удаление карты | `DELETE /v2/passes/{id}` |
| Push-уведомление | `POST /v2/passes/{id}/push` |
| Список шаблонов | `GET /v2/templates` |

## Обязательные параметры

### Старая версия:
```
✅ API Key
✅ Template ID
```

### Новая версия:
```
✅ Username (HTTP Digest)
✅ Password (HTTP Digest)
✅ Template ID
```

## Примеры запросов

### С Bearer Token (старый способ):
```http
POST /v2t/passes HTTP/1.1
Host: api.osmicards.com
Authorization: Bearer your_api_key_here
Content-Type: application/json

{...}
```

### С HTTP Digest (новый способ):
```http
POST /v2/passes HTTP/1.1
Host: api.osmicards.com
Authorization: Digest username="your_username", realm="...", nonce="...", uri="/v2/passes", response="..."
Content-Type: application/json

{...}
```

*Примечание: Bitrix HttpClient автоматически формирует правильный Digest заголовок.*

## Проверка настроек

### Через админку:
1. Укажите Username и Password
2. Нажмите **"Проверить подключение"**
3. Если все верно - увидите сообщение об успехе

### Через код:
```php
use Level44\OsmiCard\Api;

$api = new Api();

if ($api->isConfigured()) {
    echo "Настройки корректны";
} else {
    echo "Укажите Username, Password и Template ID";
}

// Тестовый запрос
$result = $api->getTemplates();
if ($result['success']) {
    echo "Авторизация успешна!";
} else {
    echo "Ошибка: " . $result['error'];
}
```

## Миграция с Bearer Token

Если вы уже настроили интеграцию с Bearer Token:

1. **Удалите старые настройки:**
   - API Key (больше не используется)
   - Secret Key (больше не используется)

2. **Добавьте новые настройки:**
   - Username (HTTP Digest)
   - Password (HTTP Digest)

3. **Проверьте URL:**
   - Было: `https://api.osmicards.com/v2t`
   - Стало: `https://api.osmicards.com/v2`

4. **Сохраните и протестируйте:**
   - Кнопка "Проверить подключение"

## Безопасность

### HTTP Digest преимущества:
✅ Пароль не передается в открытом виде  
✅ Использует хэширование  
✅ Защита от replay-атак (при использовании nonce)  
✅ Более безопасен чем Basic Authentication  

### Рекомендации:
- Используйте HTTPS (всегда)
- Храните password в безопасном месте
- Не передавайте credentials в git
- Регулярно меняйте пароль

## Хранение настроек

Настройки хранятся в опциях модуля `level44.osmicard`:

```php
use Level44\OsmiCard\Settings;

// Получить username
$username = Settings::get('api_username');

// Получить password
$password = Settings::get('api_password');

// Проверить валидность
if (Settings::isValid()) {
    echo "Настройки корректны";
}
```

## Troubleshooting

### Ошибка "401 Unauthorized"
**Причина:** Неверные Username или Password  
**Решение:** Проверьте credentials в личном кабинете OSMI Card

### Ошибка "403 Forbidden"
**Причина:** Нет доступа к ресурсу  
**Решение:** Убедитесь, что у вашего аккаунта есть необходимые права

### Ошибка "404 Not Found"
**Причина:** Неверный URL endpoint  
**Решение:** Проверьте, что URL = `https://api.osmicards.com/v2`

### "Заполните Username и Password"
**Причина:** Не указаны credentials  
**Решение:** Заполните поля Username и Password в настройках

## Логирование

Все запросы логируются в `/upload/osmi_card.log`:

**Успешный запрос:**
```
[2025-10-09 12:34:56] Успешно создана карта лояльности для пользователя ID: 123, номер карты (телефон): 79001234567
```

**Ошибка авторизации:**
```
[2025-10-09 12:35:01] Ошибка создания карты для пользователя ID: 124. Ошибка: HTTP Error 401
```

## Тестирование

### 1. Тест подключения:
```bash
Админка → Настройки → OSMI Card → "Проверить подключение"
```

### 2. Получение шаблонов:
```bash
Админка → Настройки → OSMI Card → "Получить список шаблонов"
```

### 3. Регистрация тестового пользователя:
```bash
1. Зарегистрируйте пользователя с телефоном
2. Проверьте лог /upload/osmi_card.log
3. Должна быть запись о создании карты
```

## Изменения в файлах

### `Api.php`
- ❌ Удалено: `$apiKey`, `$secretKey`
- ✅ Добавлено: `$username`, `$password`
- ✅ Изменено: URL на `/v2`
- ✅ Изменено: Авторизация на `setAuthorization()`

### `Settings.php`
- ❌ Удалено: `api_key`, `secret_key`
- ✅ Добавлено: `api_username`, `api_password`
- ✅ Изменен URL по умолчанию: `/v2`

### `osmi_card_settings.php`
- ❌ Удалены поля: API Key, Secret Key
- ✅ Добавлены поля: Username, Password
- ✅ Обновлена информация об авторизации

## Документация

- **Основная документация**: `README_UPDATED.md`
- **Телефон как номер карты**: `OSMI_CARD_PHONE_AS_SERIAL.md`
- **Быстрый старт**: `QUICKSTART_FIXED.md`

## API Reference

### Класс Api

```php
class Api {
    protected string $apiUrl;        // https://api.osmicards.com/v2
    protected string $username;      // Username для HTTP Digest
    protected string $password;      // Password для HTTP Digest
    protected string $templateId;    // ID шаблона карты
    
    public function __construct();
    public function isConfigured(): bool;
    public function registerCard(array $userData): array;
    public function getPass(string $passId): array;
    public function getPasses(array $filters = []): array;
    public function updatePass(string $passId, array $data): array;
    public function deletePass(string $passId): array;
    public function sendPushNotification(string $passId, string $message): array;
    public function getTemplates(): array;
}
```

## Версии

| Версия | Авторизация | URL | Дата |
|--------|-------------|-----|------|
| 1.0.0 | Bearer Token (неверно) | /v2t | 2025-10-09 |
| 1.1.0 | Bearer Token (исправлено) | /v2t | 2025-10-09 |
| 1.2.0 | Bearer Token | /v2t | 2025-10-09 |
| **1.3.0** | **HTTP Digest** ✅ | **/v2** ✅ | **2025-10-09** |

---

**Текущая версия**: 1.3.0  
**Авторизация**: HTTP Digest Authentication 🔐  
**Endpoint**: https://api.osmicards.com/v2  
**Статус**: ✅ Готово к использованию

