<?php

namespace Level44\Event;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Level44\OsmiCard\Api;

/**
 * Обработчики событий для интеграции с OSMI Card
 */
class OsmiCardHandlers extends HandlerBase
{
    /**
     * Регистрация обработчиков событий
     */
    public static function register(): void
    {
        // Событие после регистрации пользователя
        static::addEventHandler("main", "OnAfterUserAdd");

        // Событие после обновления пользователя (для обновления данных карты)
      //  static::addEventHandler("main", "OnAfterUserUpdate");
    }

    /**
     * Обработчик события регистрации пользователя
     * Создает новую карту лояльности в OSMI Card
     *
     * @param array $arFields Поля пользователя
     * @return bool
     */
    public static function OnAfterUserAddHandler(&$arFields): bool
    {
        try {
            // Проверяем, включена ли интеграция с OSMI Card
            if (!self::isEnabled()) {
                return true;
            }

            $userId = $arFields['ID'] ?? $arFields['USER_ID'] ?? null;

            if (!$userId) {
                self::log('Не удалось получить ID пользователя при регистрации');
                return true;
            }

            // Получаем данные пользователя
            $user = self::getUserData($userId);

            self::log("user ".json_encode($user));

            if (!$user) {
                self::log("Не удалось получить данные пользователя ID: {$userId}");
                return true;
            }

            // Проверяем наличие телефона (будет использоваться как serial_number карты)
            if (empty($user['phone'])) {
                self::log("У пользователя ID: {$userId} не указан телефон. Карта не может быть создана без номера телефона.");
                return true;
            }

            // Добавляем ID пользователя для дополнительной информации
            $user['userId'] = $userId;

            // Инициализируем API
            $api = new Api();

            // Регистрируем карту в OSMI
            $result = $api->registerCard($user);

            if ($result['success']) {
                // Сохраняем номер карты (serial_number = телефон) и ID карты в профиле пользователя
                $cardNumber = $result['data']['serial_number'] ?? $result['data']['cardNumber'] ?? null;
                $cardId = $result['data']['id'] ?? null;
                $passUrl = $result['data']['pass_url'] ?? null;

                if (!empty($cardNumber)) {
                    self::saveCardData($userId, $cardNumber, $cardId);
                    $logMessage = "Успешно создана карта лояльности для пользователя ID: {$userId}, номер карты (телефон): {$cardNumber}";
                    if ($passUrl) {
                        $logMessage .= ", URL: {$passUrl}";
                    }
                    self::log($logMessage);

                    // Опционально: обновляем поля карты данными пользователя
                    // Раскомментируйте, если нужно обновлять поля после создания
                    /*
                    if (!empty($user['email']) || !empty($user['firstName']) || !empty($user['lastName'])) {
                        $updateResult = $api->updateCardFields($cardNumber, $user);
                        if ($updateResult['success']) {
                            self::log("Поля карты {$cardNumber} обновлены данными пользователя");
                        }
                    }
                    */
                }
            } else {
                self::log("Ошибка создания карты для пользователя ID: {$userId}. Ошибка: " . ($result['error'] ?? 'Неизвестная ошибка'));
            }

        } catch (\Exception $e) {
            self::log("Исключение при создании карты: " . $e->getMessage());
        }

        return true;
    }

    /**
     * Обработчик события обновления пользователя
     *
     * @param array $arFields Поля пользователя
     * @return bool
     */
    public static function OnAfterUserUpdateHandler(&$arFields): bool
    {
        // Можно добавить логику обновления данных карты в OSMI
        // если изменились данные пользователя
        return true;
    }

    /**
     * Получает данные пользователя для регистрации карты
     *
     * @param int $userId ID пользователя
     * @return array|null
     */
    protected static function getUserData(int $userId): ?array
    {
        $rsUser = \CUser::GetByID($userId);
        $user = $rsUser->Fetch();

        if (!$user) {
            return null;
        }

        return [
            'phone'      => $user['LOGIN'],
            'email'      => $user['EMAIL'] ?? '',
            'firstName'  => $user['NAME'] ?? '',
            'lastName'   => $user['LAST_NAME'] ?? '',
            'secondName' => $user['SECOND_NAME'] ?? '',
            'birthDate'  => $user['PERSONAL_BIRTHDAY'] ? $user['PERSONAL_BIRTHDAY'] : null,
            'gender'     => $user['PERSONAL_GENDER'] ?? null,
        ];
    }



    /**
     * Сохраняет номер карты и ID карты в профиле пользователя
     *
     * @param int $userId ID пользователя
     * @param string $cardNumber Номер карты
     * @param string|null $cardId ID карты в системе OSMI
     * @return bool
     */
    protected static function saveCardData(int $userId, string $cardNumber, ?string $cardId = null): bool
    {
        $user = new \CUser();

        $updateData = [
            'UF_OSMI_CARD_NUMBER' => $cardNumber
        ];

        if (!empty($cardId)) {
            $updateData['UF_OSMI_CARD_ID'] = $cardId;
        }

        $result = $user->Update($userId, $updateData);

        return (bool)$result;
    }

    /**
     * Получает номер карты пользователя (номер телефона)
     *
     * @param int $userId ID пользователя
     * @return string|null Номер телефона (serial_number карты)
     */
    public static function getUserCardNumber(int $userId): ?string
    {
        $rsUser = \CUser::GetByID($userId);
        $user = $rsUser->Fetch();

        return $user['UF_OSMI_CARD_NUMBER'] ?? null;
    }

    /**
     * Проверяет, включена ли интеграция с OSMI Card
     *
     * @return bool
     */
    protected static function isEnabled(): bool
    {
        return Option::get('level44.osmicard', 'enabled', 'N') === 'Y';
    }

    /**
     * Логирование событий OSMI Card
     *
     * @param string $message Сообщение для лога
     * @return void
     */
    protected static function log(string $message): void
    {
        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/osmi_card.log';
        $date = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$date}] {$message}\n", FILE_APPEND);
    }
}

