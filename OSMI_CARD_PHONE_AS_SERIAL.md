# OSMI Card Integration - Номер телефона как serial_number

## 📱 Изменение: Телефон = Номер карты

В интеграции используется **номер телефона пользователя** в качестве serial_number карты.

## Как это работает

### При регистрации пользователя:

1. Проверяется наличие телефона у пользователя
2. Телефон форматируется (79001234567)
3. Отправляется запрос в OSMI Card:

```json
POST /v2t/passes
{
  "template_id": "ваш_template_id",
  "serial_number": "79001234567",  ← Номер телефона!
  "barcode": {
    "message": "79001234567",      ← Номер телефона!
    "format": "QR"
  },
  "fields": {
    "phone": "79001234567",
    "email": "user@example.com",
    "first_name": "Иван",
    "last_name": "Иванов"
  }
}
```

4. В профиль пользователя сохраняется:
   - `UF_OSMI_CARD_NUMBER` = 79001234567 (телефон)
   - `UF_OSMI_CARD_ID` = ID карты в OSMI

## ⚠️ Важно

### Телефон обязателен!

Если у пользователя не указан телефон при регистрации:
- Карта **НЕ будет создана**
- В лог запишется: "У пользователя ID: X не указан телефон. Карта не может быть создана без номера телефона."

### Формат телефона

Телефон автоматически форматируется:
- `+7 (900) 123-45-67` → `79001234567`
- `8 (900) 123-45-67` → `79001234567`
- `9001234567` → `79001234567`

## Преимущества использования телефона

✅ **Уникальность** - номер телефона уникален для каждого пользователя  
✅ **Узнаваемость** - пользователь помнит свой номер телефона  
✅ **Поиск** - легко найти карту по телефону  
✅ **Интеграция** - можно связать с другими системами по телефону  

## Примеры использования

### Получить номер карты (телефон) пользователя:

```php
use Level44\Event\OsmiCardHandlers;

$userId = $USER->GetID();
$phoneNumber = OsmiCardHandlers::getUserCardNumber($userId);

if ($phoneNumber) {
    echo "Номер вашей карты: " . $phoneNumber;
    // Выведет: Номер вашей карты: 79001234567
}
```

### Найти карту по телефону:

```php
use Level44\OsmiCard\Api;

$api = new Api();

// Получить список карт с фильтром по serial_number (телефону)
$result = $api->getPasses([
    'serial_number' => '79001234567'
]);

if ($result['success'] && !empty($result['data'])) {
    $card = $result['data'][0];
    echo "ID карты: " . $card['id'];
}
```

### Создать карту вручную с телефоном:

```php
use Level44\OsmiCard\Api;

$api = new Api();
$result = $api->registerCard([
    'phone' => '79001234567',  // Будет использован как serial_number
    'email' => 'user@example.com',
    'firstName' => 'Иван',
    'lastName' => 'Иванов',
]);

if ($result['success']) {
    echo "Карта создана с номером: " . $result['data']['serial_number'];
    // Выведет: Карта создана с номером: 79001234567
}
```

## Проверка наличия карты

### По номеру телефона:

```php
use Level44\OsmiCard\Api;

function hasCard(string $phone): bool {
    $api = new Api();
    $result = $api->getPasses(['serial_number' => $phone]);
    
    return $result['success'] && !empty($result['data']);
}

if (hasCard('79001234567')) {
    echo "У пользователя есть карта";
}
```

### По ID пользователя:

```php
use Level44\Event\OsmiCardHandlers;

function userHasCard(int $userId): bool {
    $cardNumber = OsmiCardHandlers::getUserCardNumber($userId);
    return !empty($cardNumber);
}

if (userHasCard($USER->GetID())) {
    echo "У вас есть карта лояльности";
}
```

## Логирование

В лог `/upload/osmi_card.log` записывается:

**Успех:**
```
[2025-10-09 12:34:56] Успешно создана карта лояльности для пользователя ID: 123, номер карты (телефон): 79001234567, URL: https://...
```

**Ошибка (нет телефона):**
```
[2025-10-09 12:35:01] У пользователя ID: 124 не указан телефон. Карта не может быть создана без номера телефона.
```

## Где указывается телефон при регистрации

Телефон берется из полей пользователя (в порядке приоритета):
1. `PERSONAL_PHONE` - личный телефон
2. `WORK_PHONE` - рабочий телефон

### В форме регистрации

Убедитесь, что в форме регистрации есть поле для телефона:

```html
<input type="tel" name="PERSONAL_PHONE" required>
```

### В компоненте регистрации

Проверьте настройки компонента `system.auth.registration`:
- Поле "Телефон" должно быть активно
- Желательно сделать его обязательным

## Отображение номера карты в личном кабинете

### Простой вариант:

```php
<?php
use Level44\Event\OsmiCardHandlers;

global $USER;
if ($USER->IsAuthorized()) {
    $cardNumber = OsmiCardHandlers::getUserCardNumber($USER->GetID());
    if ($cardNumber) {
        ?>
        <div class="loyalty-card">
            <h3>Ваша карта лояльности</h3>
            <p class="card-number"><?= htmlspecialchars($cardNumber) ?></p>
        </div>
        <?php
    }
}
?>
```

### Форматированный вывод:

```php
<?php
function formatPhone($phone) {
    // 79001234567 → +7 (900) 123-45-67
    if (preg_match('/^7(\d{3})(\d{3})(\d{2})(\d{2})$/', $phone, $matches)) {
        return "+7 ({$matches[1]}) {$matches[2]}-{$matches[3]}-{$matches[4]}";
    }
    return $phone;
}

$cardNumber = OsmiCardHandlers::getUserCardNumber($USER->GetID());
if ($cardNumber) {
    echo "Номер карты: " . formatPhone($cardNumber);
    // Выведет: Номер карты: +7 (900) 123-45-67
}
?>
```

## QR-код карты

Serial_number (телефон) автоматически кодируется в QR-код на карте:

```json
"barcode": {
  "message": "79001234567",
  "format": "QR"
}
```

При сканировании QR-кода получится номер телефона.

## Миграция существующих карт

Если у вас уже были созданы карты с ID пользователя как serial_number:

```php
// Скрипт для миграции (запустить один раз)
use Level44\OsmiCard\Api;
use Level44\Event\OsmiCardHandlers;

$api = new Api();

// Получаем всех пользователей с картами
$rsUsers = CUser::GetList(
    $by = "id", 
    $order = "asc",
    ['!UF_OSMI_CARD_ID' => false]
);

while ($user = $rsUsers->Fetch()) {
    $userId = $user['ID'];
    $oldCardId = $user['UF_OSMI_CARD_ID'];
    $phone = $user['PERSONAL_PHONE'] ?: $user['WORK_PHONE'];
    
    if (empty($phone)) {
        continue; // Пропускаем пользователей без телефона
    }
    
    // Обновляем serial_number в OSMI
    $result = $api->updatePass($oldCardId, [
        'serial_number' => formatPhoneForCard($phone)
    ]);
    
    if ($result['success']) {
        // Обновляем в Bitrix
        $obUser = new CUser;
        $obUser->Update($userId, [
            'UF_OSMI_CARD_NUMBER' => formatPhoneForCard($phone)
        ]);
        
        echo "Обновлен пользователь ID: {$userId}\n";
    }
}
```

## Проверка работы

### 1. Регистрация с телефоном:
```
1. Зарегистрируйте пользователя с телефоном +7 (900) 123-45-67
2. Проверьте лог: /upload/osmi_card.log
3. Должна быть запись с номером 79001234567
```

### 2. Регистрация без телефона:
```
1. Попробуйте зарегистрироваться без телефона
2. В логе должна быть ошибка о том, что телефон обязателен
3. Карта не создастся
```

### 3. Проверка в профиле:
```
1. Откройте профиль пользователя в админке
2. Найдите поле "Номер карты OSMI"
3. Там должен быть номер телефона: 79001234567
```

## Требования

✅ У пользователя **ОБЯЗАТЕЛЬНО** должен быть указан телефон  
✅ Телефон должен быть в поле `PERSONAL_PHONE` или `WORK_PHONE`  
✅ Телефон автоматически форматируется в формат 7XXXXXXXXXX  

## Документация

- **Основная документация**: `README_UPDATED.md`
- **Быстрый старт**: `QUICKSTART_FIXED.md`
- **Что исправлено**: `/OSMI_CARD_FIXED.md`

---

**Версия**: 1.2.0  
**Дата**: 2025-10-09  
**Изменение**: Serial number = Номер телефона пользователя 📱

