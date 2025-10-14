# OSMI Card - Решение проблем

## ❌ Ошибка: "Invalid JSON response"

Эта ошибка означает, что API OSMI Card возвращает не JSON (возможно, HTML страницу ошибки).

### 🔍 Шаг 1: Запустите тест подключения

Откройте в браузере:
```
https://ваш-сайт.ru/local/php_interface/lib/Level44/OsmiCard/test_connection.php
```

Этот скрипт покажет:
- ✅ Статус всех настроек
- 📊 Результаты тестовых запросов
- 📝 Содержимое лог файлов
- 🔐 Результат HTTP Digest авторизации

### 🔍 Шаг 2: Проверьте лог файлы

Откройте файл:
```
/upload/osmi_card_api.log
```

В нем будет детальная информация:
- HTTP статус ответа
- Длина ответа
- Первые 500 символов ответа
- Текст ошибки

### 🔧 Частые причины и решения

#### 1️⃣ Проблема: API возвращает HTML вместо JSON

**Симптом:** В логе видно `<html>` или `<!DOCTYPE>`

**Причина:** Неверная авторизация или неправильный URL

**Решение:**
1. Проверьте Username и Password в настройках
2. Убедитесь, что URL = `https://api.osmicards.com/v2`
3. Проверьте, что credentials действительны в личном кабинете OSMI Card

#### 2️⃣ Проблема: HTTP Status 401 (Unauthorized)

**Симптом:** В логе `HTTP Status: 401`

**Причина:** Неверные Username или Password

**Решение:**
1. Перейдите в личный кабинет OSMI Card
2. Скопируйте правильные Username и Password
3. Вставьте в настройки модуля
4. Сохраните

#### 3️⃣ Проблема: HTTP Status 404 (Not Found)

**Симптом:** В логе `HTTP Status: 404`

**Причина:** Неверный URL endpoint

**Решение:**
Проверьте URL API:
- ✅ Правильно: `https://api.osmicards.com/v2`
- ❌ Неправильно: `https://api.osmicards.com/v2t`

#### 4️⃣ Проблема: Empty response

**Симптом:** В логе `Empty response from API`

**Причина:** Сервер не вернул данные

**Решение:**
1. Проверьте подключение к интернету
2. Убедитесь, что API OSMI Card доступен
3. Проверьте firewall/прокси настройки

#### 5️⃣ Проблема: Connection timeout

**Симптом:** Долгая загрузка, затем ошибка

**Причина:** Проблемы с сетью или сервер не отвечает

**Решение:**
1. Увеличьте timeout в Api.php (по умолчанию 30 секунд)
2. Проверьте доступность api.osmicards.com

### 📝 Как читать лог

#### Пример успешного запроса:
```
[2025-10-09 12:34:56] DEBUG: GET Request: https://api.osmicards.com/v2/templates
[2025-10-09 12:34:56] DEBUG: Username: your_username
[2025-10-09 12:34:56] DEBUG: HTTP Status: 200
[2025-10-09 12:34:56] DEBUG: Response length: 1234
[2025-10-09 12:34:56] DEBUG: Response preview: {"data":[{"id":"template_123","name":"My Template"}]}
```

#### Пример ошибки авторизации:
```
[2025-10-09 12:34:56] DEBUG: GET Request: https://api.osmicards.com/v2/templates
[2025-10-09 12:34:56] DEBUG: Username: wrong_username
[2025-10-09 12:34:56] DEBUG: HTTP Status: 401
[2025-10-09 12:34:56] DEBUG: Response length: 234
[2025-10-09 12:34:56] DEBUG: Response preview: <!DOCTYPE html><html><head><title>401 Unauthorized</title>
[2025-10-09 12:34:56] ERROR: Invalid JSON response (Status: 401). Response: <!DOCTYPE html>...
```

## 🔐 HTTP Digest Authentication

### Проверка работы Digest

Тестовый скрипт (`test_connection.php`) выполнит проверку HTTP Digest с помощью cURL.

Если cURL тест успешен (HTTP 200), но через HttpClient не работает:
- Возможно, проблема в реализации Digest в HttpClient
- Попробуйте использовать cURL напрямую

### Альтернатива: использование cURL

Если HttpClient не работает с Digest, можно переделать на cURL:

```php
// В Api.php
protected function get(string $endpoint, array $params = []): array
{
    $url = $this->apiUrl . $endpoint;
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
    curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Дальше parseResponse...
}
```

## 🧪 Тестирование вручную

### Через командную строку (curl):

```bash
curl -v --digest --user "username:password" \
  https://api.osmicards.com/v2/templates
```

Если работает - проблема в коде.  
Если не работает - проблема в credentials.

### Через Postman:

1. Создайте GET запрос: `https://api.osmicards.com/v2/templates`
2. Authorization → Type: **Digest Auth**
3. Укажите Username и Password
4. Send

## 📊 Чеклист диагностики

Пройдитесь по этому списку:

- [ ] Username указан и правильный
- [ ] Password указан и правильный
- [ ] URL = `https://api.osmicards.com/v2` (без `/t`)
- [ ] Template ID указан
- [ ] Интеграция включена (галочка)
- [ ] Credentials действительны в личном кабинете OSMI
- [ ] Нет блокировки на уровне firewall/прокси
- [ ] Сервер OSMI доступен (проверить ping/curl)
- [ ] Лог файл `/upload/osmi_card_api.log` содержит записи
- [ ] HTTP Status в логе = 200 (успех) или другой?

## 🛠 Решения конкретных проблем

### "Invalid JSON response" + HTML в ответе

**Что делать:**
1. Скопируйте HTML из лога
2. Посмотрите, что там написано
3. Обычно это страница ошибки 401/403/404

**Решение:**
- 401 → Проверьте credentials
- 403 → Проверьте права доступа к API
- 404 → Проверьте URL

### Username/Password правильные, но не работает

**Что делать:**
1. Убедитесь, что используется именно HTTP Digest, а не Basic
2. Проверьте, что в личном кабинете включен доступ к API
3. Попробуйте пересоздать credentials
4. Проверьте, нет ли ограничений по IP

### Все настройки правильные, curl работает, но скрипт - нет

**Что делать:**
1. Проблема в HttpClient реализации Digest
2. Переделайте на cURL (см. выше)
3. Или обратитесь в поддержку OSMI Card

## 📞 Поддержка

### Лог файлы для отправки в поддержку:

```
/upload/osmi_card_api.log - детальный лог API запросов
/upload/osmi_card.log - основной лог интеграции
```

### Информация для отправки:

1. Скриншот настроек (без Password!)
2. Последние 50 строк из `/upload/osmi_card_api.log`
3. Результат работы `test_connection.php`
4. Результат curl команды (если пробовали)

## 📖 Дополнительные ресурсы

- **Тестовый скрипт**: `/local/php_interface/lib/Level44/OsmiCard/test_connection.php`
- **Документация API**: https://apidocs.osmicards.com/
- **HTTP Digest**: https://en.wikipedia.org/wiki/Digest_access_authentication

---

## ⚡ Быстрое решение

**Если нет времени разбираться:**

1. Откройте: `https://ваш-сайт.ru/local/php_interface/lib/Level44/OsmiCard/test_connection.php`
2. Скопируйте всю страницу
3. Отправьте разработчику или в поддержку OSMI Card
4. Они скажут, в чем проблема

---

**Дата**: 2025-10-09  
**Версия**: 1.3.0

