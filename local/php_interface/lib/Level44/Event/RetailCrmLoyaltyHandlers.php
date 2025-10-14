<?php

namespace Level44\Event;

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use CUser;
use Exception;

/**
 * Обработчики событий для синхронизации данных лояльности из RetailCRM
 * 
 * Этот класс отвечает за:
 * - Получение бонусных баллов из RetailCRM
 * - Получение уровня лояльности
 * - Обновление полей пользователя в Битрикс
 */
class RetailCrmLoyaltyHandlers extends HandlerBase
{
    /**
     * Хранилище старых значений бонусов пользователей (для отслеживания изменений)
     * @var array
     */
    protected static $oldBonusValues = [];

    /**
     * Регистрация обработчиков событий
     */
    public static function register(): void
    {
        // Используем глобальную функцию-хук RetailCRM для обработки после сохранения клиента
        // Эта функция вызывается из модуля RetailCRM после обновления клиента
        global $retailCrmAfterCustomerSaveHandlers;
        
        if (!isset($retailCrmAfterCustomerSaveHandlers)) {
            $retailCrmAfterCustomerSaveHandlers = [];
        }
        
        $retailCrmAfterCustomerSaveHandlers[] = [static::class, 'onAfterCustomerSave'];
        
        // Регистрируем события для двусторонней синхронизации бонусов
        // Используем API методы RetailCRM v5:
        // - /api/v5/loyalty/account/{id}/bonus/credit (начисление)
        // - /api/v5/loyalty/account/{id}/bonus/charge (списание)
        static::addEventHandler("main", "OnBeforeUserUpdate"); // Сохраняем старое значение
        static::addEventHandler("main", "OnAfterUserUpdate");  // Синхронизируем с RetailCRM
    }

    /**
     * Обработчик после сохранения клиента из RetailCRM
     * Вызывается через хук retailCrmAfterCustomerSave
     *
     * @param array $customer Данные клиента из RetailCRM
     * @return void
     */
    public static function onAfterCustomerSave($customer): void
    {
        try {
            // ОТЛАДКА: Логируем сам факт вызова
            self::log("=== onAfterCustomerSave CALLED ===");
            self::log("Customer data: " . json_encode($customer));
            
            // Проверяем, что у клиента есть ID в Битрикс
            if (empty($customer['externalId'])) {
                self::log("WARNING: externalId is empty, skipping");
                return;
            }

            $userId = (int)$customer['externalId'];
            self::log("Processing user ID: {$userId}");
            
            // Проверяем, включена ли программа лояльности
            if (!self::isLoyaltyEnabled()) {
                self::log("WARNING: Loyalty program is disabled");
                return;
            }

            self::log("Loyalty program is enabled");

            // Получаем данные лояльности из RetailCRM
            $loyaltyData = self::getLoyaltyData($userId);
            
            if (empty($loyaltyData)) {
                self::log("WARNING: No loyalty data received for user {$userId}");
                return;
            }

            self::log("Loyalty data received: " . json_encode($loyaltyData));

            // Обновляем пользователя
            $result = self::updateUserLoyaltyFields($userId, $loyaltyData);
            
            if ($result) {
                self::log("SUCCESS: Loyalty data updated for user {$userId}");
            } else {
                self::log("ERROR: Failed to update user {$userId}");
            }
            
        } catch (Exception $e) {
            self::log("EXCEPTION: " . $e->getMessage());
            self::log("Stack trace: " . $e->getTraceAsString());
        }
    }

    /**
     * Проверка, включена ли программа лояльности в модуле RetailCRM
     *
     * @return bool
     */
    protected static function isLoyaltyEnabled(): bool
    {
        if (!Loader::includeModule('intaro.retailcrm')) {
            return false;
        }

        try {
            if (class_exists('\Intaro\RetailCrm\Component\ConfigProvider')) {
                return \Intaro\RetailCrm\Component\ConfigProvider::getLoyaltyProgramStatus() === 'Y';
            }
        } catch (Exception $e) {
            self::log("Error checking loyalty status: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Получение данных лояльности клиента из RetailCRM
     *
     * @param int $userId ID пользователя в Битрикс
     * @return array Массив с данными лояльности
     */
    protected static function getLoyaltyData(int $userId): array
    {
        $loyaltyData = [];

        try {
            if (!Loader::includeModule('intaro.retailcrm')) {
                self::log("Module intaro.retailcrm not loaded");
                return $loyaltyData;
            }

            // Проверяем наличие необходимых классов
            if (!class_exists('\Intaro\RetailCrm\Component\Factory\ClientFactory')) {
                self::log("ClientFactory class not found");
                return $loyaltyData;
            }

            if (!class_exists('\Intaro\RetailCrm\Component\ConfigProvider')) {
                self::log("ConfigProvider class not found");
                return $loyaltyData;
            }

            self::log("Creating ClientAdapter...");

            // Создаем API клиент используя новый ClientAdapter (как в модуле)
            $client = \Intaro\RetailCrm\Component\Factory\ClientFactory::createClientAdapter();
            
            if (!$client) {
                self::log("Failed to create ClientAdapter");
                return $loyaltyData;
            }

            // Получаем сайт из настроек
            $sitesAvailable = \Intaro\RetailCrm\Component\ConfigProvider::getSitesAvailable();
            
            // Создаем запрос (как в LoyaltyAccountService::activateLpUserInBitrix)
            $getRequest = new \Intaro\RetailCrm\Model\Api\Request\Loyalty\Account\LoyaltyAccountRequest();
            $getRequest->filter = new \Intaro\RetailCrm\Model\Api\LoyaltyAccountApiFilterType();
            $getRequest->filter->sites = is_array($sitesAvailable) ? $sitesAvailable : [$sitesAvailable];
            $getRequest->filter->customerExternalId = (string)$userId;

            self::log("Calling getLoyaltyAccounts for user {$userId}...");

            // Запрашиваем аккаунты лояльности
            $response = $client->getLoyaltyAccounts($getRequest);
            
            if (!$response) {
                self::log("No response from API");
                return $loyaltyData;
            }

            if (empty($response->loyaltyAccounts)) {
                self::log("No loyaltyAccounts in response");
                return $loyaltyData;
            }

            $loyaltyAccounts = $response->loyaltyAccounts;
            
            if (empty($loyaltyAccounts) || !is_array($loyaltyAccounts)) {
                self::log("loyaltyAccounts is empty or not array");
                return $loyaltyData;
            }

            self::log("Found " . count($loyaltyAccounts) . " loyalty account(s)");

            // Берем первый аккаунт лояльности
            $loyaltyAccount = reset($loyaltyAccounts);

            // Извлекаем бонусные баллы
            if (isset($loyaltyAccount->amount)) {
                $loyaltyData['UF_BONUS_AMOUNT_INTARO'] = (float)$loyaltyAccount->amount;
                self::log("Amount: {$loyaltyAccount->amount}");
            }

            // Извлекаем данные об уровне лояльности
            if (isset($loyaltyAccount->loyaltyLevel)) {
                $level = $loyaltyAccount->loyaltyLevel;
                
                if (isset($level->id)) {
                    $loyaltyData['UF_LOYALTY_LEVEL_ID_INTARO'] = (string)$level->id;
                    self::log("Level ID: {$level->id}");
                }
                
                if (isset($level->name)) {
                    $loyaltyData['UF_LOYALTY_LEVEL_NAME_INTARO'] = (string)$level->name;
                    self::log("Level name: {$level->name}");
                }
            }

            // Обновляем ID аккаунта лояльности, если он есть
            if (isset($loyaltyAccount->id)) {
                $loyaltyData['UF_LP_ID_INTARO'] = (int)$loyaltyAccount->id;
                self::log("Loyalty account ID: {$loyaltyAccount->id}");
            }

        } catch (Exception $e) {
            self::log("EXCEPTION in getLoyaltyData: " . $e->getMessage());
            self::log("Stack: " . $e->getTraceAsString());
        }

        return $loyaltyData;
    }

    /**
     * Обновление полей лояльности пользователя
     *
     * @param int $userId ID пользователя
     * @param array $loyaltyData Данные лояльности
     * @return bool
     */
    protected static function updateUserLoyaltyFields(int $userId, array $loyaltyData): bool
    {
        if (empty($loyaltyData)) {
            return false;
        }

        $user = new CUser();
        $result = $user->Update($userId, $loyaltyData);

        if (!$result) {
            self::log("Error updating user {$userId}: " . $user->LAST_ERROR);
            return false;
        }

        return true;
    }

    /**
     * Обработчик ПЕРЕД изменением пользователя (OnBeforeUserUpdate)
     * Сохраняет текущее значение бонусов для последующего сравнения
     *
     * @param array $arFields Данные пользователя
     * @return bool
     */
    public static function OnBeforeUserUpdateHandler(&$arFields): bool
    {
        try {
            // Пропускаем, если это синхронизация из RetailCRM (избегаем циклов)
            if (isset($GLOBALS['RETAIL_CRM_HISTORY']) && $GLOBALS['RETAIL_CRM_HISTORY'] === true) {
                return true;
            }

            $userId = $arFields['ID'] ?? null;
            if (!$userId) {
                return true;
            }

            // Сохраняем текущее значение бонусов
            $rsUser = \CUser::GetByID($userId);
            $oldUser = $rsUser->Fetch();
            
            if ($oldUser) {
                self::$oldBonusValues[$userId] = (float)($oldUser['UF_BONUS_AMOUNT_INTARO'] ?? 0);
            }

        } catch (Exception $e) {
            self::log("EXCEPTION in OnBeforeUserUpdateHandler: " . $e->getMessage());
        }

        return true;
    }

    /**
     * Обработчик ПОСЛЕ изменения пользователя (OnAfterUserUpdate)
     * Синхронизирует изменения бонусов из Битрикс в RetailCRM
     *
     * @param array $arFields Новые данные пользователя
     * @return bool
     */
    public static function OnAfterUserUpdateHandler(&$arFields): bool
    {
        try {
            // Пропускаем, если это синхронизация из RetailCRM (избегаем циклов)
            if (isset($GLOBALS['RETAIL_CRM_HISTORY']) && $GLOBALS['RETAIL_CRM_HISTORY'] === true) {
                return true;
            }

            // Проверяем, что изменилось поле бонусов
            if (!isset($arFields['UF_BONUS_AMOUNT_INTARO'])) {
                return true;
            }

            $userId = $arFields['ID'] ?? null;
            if (!$userId) {
                return true;
            }

            // Проверяем, включена ли программа лояльности
            if (!self::isLoyaltyEnabled()) {
                return true;
            }

            self::log("=== OnAfterUserUpdate: Bonus change detected for user {$userId} ===");

            // Получаем старое значение из сохраненного
            $oldBonuses = self::$oldBonusValues[$userId] ?? null;
            
            if ($oldBonuses === null) {
                self::log("WARNING: Old bonus value not found, skipping sync");
                return true;
            }

            $newBonuses = (float)$arFields['UF_BONUS_AMOUNT_INTARO'];
            
            // Если изменений нет, выходим
            if ($oldBonuses === $newBonuses) {
                self::log("No changes in bonus amount");
                unset(self::$oldBonusValues[$userId]);
                return true;
            }

            $difference = $newBonuses - $oldBonuses;
            
            self::log("Old bonuses: {$oldBonuses}, New bonuses: {$newBonuses}, Difference: {$difference}");

            // Синхронизируем с RetailCRM
            $result = self::syncBonusesToRetailCrm($userId, $difference);
            
            if ($result) {
                self::log("SUCCESS: Bonuses synced to RetailCRM for user {$userId}");
            } else {
                self::log("ERROR: Failed to sync bonuses to RetailCRM");
            }

            // Очищаем сохраненное значение
            unset(self::$oldBonusValues[$userId]);

        } catch (Exception $e) {
            self::log("EXCEPTION in OnAfterUserUpdateHandler: " . $e->getMessage());
        }

        return true;
    }

    /**
     * Синхронизация бонусов в RetailCRM
     * Использует API методы RetailCRM v5:
     * - /api/v5/loyalty/account/{id}/bonus/credit - начисление
     * - /api/v5/loyalty/account/{id}/bonus/charge - списание
     *
     * @param int $userId ID пользователя
     * @param float $amount Сумма начисления (>0) или списания (<0)
     * @return bool
     */
    protected static function syncBonusesToRetailCrm(int $userId, float $amount): bool
    {
        try {
            if (!Loader::includeModule('intaro.retailcrm')) {
                return false;
            }

            // Получаем ID аккаунта лояльности
            $rsUser = \CUser::GetByID($userId);
            $arUser = $rsUser->Fetch();
            
            if (!$arUser || empty($arUser['UF_LP_ID_INTARO'])) {
                self::log("WARNING: User {$userId} has no loyalty account ID");
                return false;
            }

            $loyaltyAccountId = (int)$arUser['UF_LP_ID_INTARO'];
            
            // Определяем тип операции: начисление или списание
            if ($amount > 0) {
                return self::creditBonuses($loyaltyAccountId, $amount, $userId);
            } elseif ($amount < 0) {
                return self::chargeBonuses($loyaltyAccountId, abs($amount), $userId);
            }

            return true;

        } catch (Exception $e) {
            self::log("EXCEPTION in syncBonusesToRetailCrm: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Начисление бонусов в RetailCRM
     * API: POST /api/v5/loyalty/account/{id}/bonus/credit
     *
     * @param int $loyaltyAccountId ID аккаунта лояльности
     * @param float $amount Сумма начисления
     * @param int $userId ID пользователя (для лога)
     * @return bool
     */
    protected static function creditBonuses(int $loyaltyAccountId, float $amount, int $userId): bool
    {
        try {
            self::log("Crediting {$amount} bonuses to loyalty account {$loyaltyAccountId} (user {$userId})");

            // Получаем конфигурацию API
            $apiUrl = \RetailcrmConfigProvider::getApiUrl();
            $apiKey = \RetailcrmConfigProvider::getApiKey();
            
            if (empty($apiUrl) || empty($apiKey)) {
                self::log("ERROR: API credentials not configured");
                return false;
            }

            // Формируем полный URL для запроса
            if ('/' !== $apiUrl[strlen($apiUrl) - 1]) {
                $apiUrl .= '/';
            }
            
            // Создаем HTTP клиент
            $client = new \RetailCrm\Http\Client($apiUrl . 'api/v5', ['apiKey' => $apiKey]);
            
            // Формируем данные запроса согласно документации RetailCRM API v5
            $data = [
                'amount' => $amount,
                'comment' => 'Начисление бонусов из Битрикс (ручное изменение)'
            ];

            // Выполняем запрос к правильному endpoint
            $response = $client->makeRequest(
                "/loyalty/account/{$loyaltyAccountId}/bonus/credit",
                \RetailCrm\Http\Client::METHOD_POST,
                $data
            );

            if ($response && $response->isSuccessful()) {
                self::log("SUCCESS: Credited {$amount} bonuses to loyalty account {$loyaltyAccountId}");
                return true;
            } else {
                $errors = $response ? json_encode($response->getErrors()) : 'No response';
                $errorMsg = $response ? $response->getErrorMsg() : 'No response';
                self::log("ERROR: Failed to credit bonuses. Message: {$errorMsg}, Errors: {$errors}");
                return false;
            }

        } catch (Exception $e) {
            self::log("EXCEPTION in creditBonuses: " . $e->getMessage());
            self::log("Stack: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Списание бонусов в RetailCRM  
     * API: POST /api/v5/loyalty/account/{id}/bonus/charge
     *
     * @param int $loyaltyAccountId ID аккаунта лояльности
     * @param float $amount Сумма списания (положительное число)
     * @param int $userId ID пользователя (для лога)
     * @return bool
     */
    protected static function chargeBonuses(int $loyaltyAccountId, float $amount, int $userId): bool
    {
        try {
            self::log("Charging {$amount} bonuses from loyalty account {$loyaltyAccountId} (user {$userId})");

            // Получаем конфигурацию API
            $apiUrl = \RetailcrmConfigProvider::getApiUrl();
            $apiKey = \RetailcrmConfigProvider::getApiKey();
            
            if (empty($apiUrl) || empty($apiKey)) {
                self::log("ERROR: API credentials not configured");
                return false;
            }

            // Формируем полный URL для запроса
            if ('/' !== $apiUrl[strlen($apiUrl) - 1]) {
                $apiUrl .= '/';
            }
            
            // Создаем HTTP клиент
            $client = new \RetailCrm\Http\Client($apiUrl . 'api/v5', ['apiKey' => $apiKey]);
            
            // Формируем данные запроса согласно документации RetailCRM API v5
            $data = [
                'amount' => $amount,
                'comment' => 'Списание бонусов из Битрикс (ручное изменение)'
            ];

            // Выполняем запрос к правильному endpoint (charge, а не debit!)
            $response = $client->makeRequest(
                "/loyalty/account/{$loyaltyAccountId}/bonus/charge",
                \RetailCrm\Http\Client::METHOD_POST,
                $data
            );

            if ($response && $response->isSuccessful()) {
                self::log("SUCCESS: Charged {$amount} bonuses from loyalty account {$loyaltyAccountId}");
                return true;
            } else {
                $errors = $response ? json_encode($response->getErrors()) : 'No response';
                $errorMsg = $response ? $response->getErrorMsg() : 'No response';
                self::log("ERROR: Failed to charge bonuses. Message: {$errorMsg}, Errors: {$errors}");
                return false;
            }

        } catch (Exception $e) {
            self::log("EXCEPTION in chargeBonuses: " . $e->getMessage());
            self::log("Stack: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Логирование
     *
     * @param string $message Сообщение для лога
     * @return void
     */
    protected static function log(string $message): void
    {
        try {
            if (Loader::includeModule('intaro.retailcrm')) {
                \Logger::getInstance()->write($message, 'loyalty_sync');
            }
        } catch (Exception $e) {
            // Fallback на обычный лог
            $logFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/loyalty_sync.log';
            $logMessage = date('[Y-m-d H:i:s] ') . $message . PHP_EOL;
            file_put_contents($logFile, $logMessage, FILE_APPEND);
        }
    }
}

