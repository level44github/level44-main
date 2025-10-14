<?php

namespace Level44\OsmiCard;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

/**
 * Класс для работы с API OSMI Card
 * Документация: https://apidocs.osmicards.com/
 */
class Api
{
    /** @var string URL API OSMI Card */
    protected string $apiUrl;

    /** @var string Username для Digest авторизации */
    protected string $username;

    /** @var string Password для Digest авторизации */
    protected string $password;

    /** @var int ID проекта */
    protected int $projectId;

    /** @var HttpClient HTTP клиент */
    protected HttpClient $httpClient;

    /** @var string ID шаблона карты */
    protected string $templateId;

    /** @var int Последний HTTP статус для совместимости */
    protected int $lastHttpStatus = 0;

    /**
     * Конструктор
     */
    public function __construct()
    {
        // Получаем настройки из опций модуля
        $this->apiUrl = Option::get('level44.osmicard', 'api_url', 'https://vm-api.osmicards.com/v2t');
        $this->username = Option::get('level44.osmicard', 'api_username', '');
        $this->password = Option::get('level44.osmicard', 'api_password', '');
        $this->projectId = (int)Option::get('level44.osmicard', 'project_id', 0);
        $this->templateId = Option::get('level44.osmicard', 'template_id', '');

        $this->httpClient = new HttpClient([
            'socketTimeout' => 30,
            'streamTimeout' => 30,
        ]);

        // HTTP Digest авторизация
        if (!empty($this->username) && !empty($this->password)) {
            $this->httpClient->setAuthorization($this->username, $this->password);
        }

        $this->httpClient->setHeader('Content-Type', 'application/json');
    }

    /**
     * Создание новой карты (pass) через POST /passes/{serial_number}/{template_id}
     * Документация: https://apidocs.osmicards.com/
     * Пример: POST /passes/79001234567/CreaConcept?withValues=true
     *
     * @param array $userData Данные пользователя
     * @return array Результат операции
     */
    public function registerCard(array $userData): array
    {
        try {
            if (empty($this->templateId)) {
                return [
                    'success' => false,
                    'error' => 'Не указан template_id. Укажите ID шаблона в настройках.',
                ];
            }

            // Используем номер телефона как серийный номер карты
            $serialNumber = $userData['phone'] ?? null;

            if (empty($serialNumber)) {
                return [
                    'success' => false,
                    'error' => 'Не указан номер телефона. Телефон обязателен для создания карты.',
                ];
            }

            // Правильный endpoint: POST /passes/{serial_number}/{template_id}?withValues=true
            $endpoint = '/passes/' .$serialNumber . '/' . urlencode($this->templateId);

            // Подготавливаем данные согласно документации OSMI Card API
            // Формат: массив values с объектами {label, value}
            $requestData = [
                'noSharing' => false,
                'values' => [],
            ];

            // Добавляем данные пользователя в values
            if (!empty($userData['firstName']) || !empty($userData['lastName'])) {
                $fullName = trim(($userData['firstName'] ?? '') . ' ' . ($userData['lastName'] ?? ''));
                if (!empty($fullName)) {
                    $requestData['values'][] = [
                        'label' => 'КЛИЕНТ',
                        'value' => $fullName,
                    ];
                }
            }

           /* if (!empty($userData['email'])) {
                $requestData['values'][] = [
                    'label' => 'Email',
                    'value' => $userData['email'],
                ];
            }*/

            /*if (!empty($userData['phone'])) {
                $requestData['values'][] = [
                    'label' => 'Телефон',
                    'value' => $userData['phone'],
                ];
            }*/

            // Barcode с серийным номером
            $requestData['barcode'] = [
                'show' => true,
                'showSignature' => true,
                'message' => $serialNumber,
                'signature' => 'NUM ' . $serialNumber,
            ];

            $response = $this->postWithParams($endpoint, $requestData, ['withValues' => 'true']);

            if (isset($response['error']) || isset($response['errors'])) {
                // Проверяем код ошибки 319 - карта уже существует
                if (isset($response['code']) && $response['code'] == 319) {
                    return [
                        'success' => false,
                        'error' => 'Карта с таким номером уже существует',
                        'code' => 319,
                    ];
                }

                return [
                    'success' => false,
                    'error' => $response['error'] ?? $response['message'] ?? $response['RMESSAGE'] ?? 'Неизвестная ошибка',
                    'code' => $response['code'] ?? $response['RCODE'] ?? null,
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'id' => $response['id'] ?? null,
                    'serial_number' => $serialNumber,
                    'cardNumber' => $serialNumber,
                    'pass_url' => $response['pass_url'] ?? $response['url'] ?? null,
                    'full_response' => $response,
                ],
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Получение информации о карте (pass) по ID
     * GET /passes/{pass_id}
     *
     * @param string $passId ID карты
     * @return array
     */
    public function getPass(string $passId): array
    {
        try {
            $response = $this->get('/passes/' . $passId);

            if (isset($response['error']) || isset($response['errors'])) {
                return [
                    'success' => false,
                    'error' => $response['error'] ?? $response['message'] ?? 'Неизвестная ошибка',
                ];
            }

            return [
                'success' => true,
                'data' => $response,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Получение списка карт (passes)
     * GET /passes
     *
     * @param array $filters Фильтры (serial_number, template_id и т.д.)
     * @return array
     */
    public function getPasses(array $filters = []): array
    {
        try {
            $response = $this->get('/passes', $filters);

            if (isset($response['error']) || isset($response['errors'])) {
                return [
                    'success' => false,
                    'error' => $response['error'] ?? $response['message'] ?? 'Неизвестная ошибка',
                ];
            }

            return [
                'success' => true,
                'data' => $response['data'] ?? $response,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Обновление карты (pass)
     * PUT /passes/{serial_number}
     *
     * @param string $serialNumber Serial number карты (номер телефона)
     * @param array $fields Поля для обновления
     * @return array
     */
    public function updatePass(string $serialNumber, array $fields): array
    {
        try {
            $endpoint = '/passes/' . urlencode($serialNumber);
            $response = $this->put($endpoint, $fields);

            if (isset($response['error']) || isset($response['errors'])) {
                return [
                    'success' => false,
                    'error' => $response['error'] ?? $response['message'] ?? $response['RMESSAGE'] ?? 'Неизвестная ошибка',
                ];
            }

            return [
                'success' => true,
                'data' => $response,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Обновление полей карты после создания
     *
     * @param string $serialNumber Serial number карты
     * @param array $userData Данные пользователя
     * @return array
     */
    public function updateCardFields(string $serialNumber, array $userData): array
    {
        $fields = [];

        if (!empty($userData['email'])) {
            $fields['email'] = $userData['email'];
        }

        if (!empty($userData['firstName'])) {
            $fields['first_name'] = $userData['firstName'];
        }

        if (!empty($userData['lastName'])) {
            $fields['last_name'] = $userData['lastName'];
        }

        if (!empty($userData['secondName'])) {
            $fields['middle_name'] = $userData['secondName'];
        }

        if (empty($fields)) {
            return [
                'success' => true,
                'message' => 'Нет полей для обновления',
            ];
        }

        return $this->updatePass($serialNumber, $fields);
    }

    /**
     * Удаление карты (pass)
     * DELETE /passes/{pass_id}
     *
     * @param string $passId ID карты
     * @return array
     */
    public function deletePass(string $passId): array
    {
        try {
            $response = $this->delete('/passes/' . $passId);

            if (isset($response['error']) || isset($response['errors'])) {
                return [
                    'success' => false,
                    'error' => $response['error'] ?? $response['message'] ?? 'Неизвестная ошибка',
                ];
            }

            return [
                'success' => true,
                'data' => $response,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Отправка push-уведомления на карту
     * POST /passes/{pass_id}/push
     *
     * @param string $passId ID карты
     * @param string $message Текст уведомления
     * @return array
     */
    public function sendPushNotification(string $passId, string $message): array
    {
        try {
            $requestData = [
                'message' => $message,
            ];

            $response = $this->post('/passes/' . $passId . '/push', $requestData);

            if (isset($response['error']) || isset($response['errors'])) {
                return [
                    'success' => false,
                    'error' => $response['error'] ?? $response['message'] ?? 'Неизвестная ошибка',
                ];
            }

            return [
                'success' => true,
                'data' => $response,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Выполнение GET запроса к API
     *
     * @param string $endpoint Endpoint API
     * @param array $params Параметры запроса
     * @return array
     */
    protected function get(string $endpoint, array $params = []): array
    {
        $url = $this->apiUrl . $endpoint;

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $this->logDebug("GET Request: {$url}");
        $this->logDebug("Username: " . (!empty($this->username) ? $this->username : 'NOT SET'));

        // Используем cURL для корректной работы с HTTP Digest
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception('cURL Error: ' . $error);
        }

        curl_close($ch);

        // Создаем mock объект для совместимости с parseResponse
        $this->lastHttpStatus = $httpStatus;

        return $this->parseResponseCurl($response, $httpStatus);
    }

    /**
     * Выполнение POST запроса к API
     *
     * @param string $endpoint Endpoint API
     * @param array $data Данные для отправки
     * @return array
     */
    protected function post(string $endpoint, array $data = []): array
    {
        $url = $this->apiUrl . $endpoint;
        $jsonData = Json::encode($data);

        $this->logDebug("POST Request: {$url}");
        $this->logDebug("Username: " . (!empty($this->username) ? $this->username : 'NOT SET'));
        $this->logDebug("Data: " . substr($jsonData, 0, 500));

        // Используем cURL для корректной работы с HTTP Digest
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception('cURL Error: ' . $error);
        }

        curl_close($ch);

        $this->lastHttpStatus = $httpStatus;

        return $this->parseResponseCurl($response, $httpStatus);
    }

    /**
     * Выполнение PUT запроса к API
     *
     * @param string $endpoint Endpoint API
     * @param array $data Данные для отправки
     * @return array
     */
    protected function put(string $endpoint, array $data = []): array
    {
        $url = $this->apiUrl . $endpoint;
        $jsonData = Json::encode($data);

        // Используем cURL для корректной работы с HTTP Digest
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception('cURL Error: ' . $error);
        }

        curl_close($ch);

        $this->lastHttpStatus = $httpStatus;

        return $this->parseResponseCurl($response, $httpStatus);
    }

    /**
     * Выполнение POST запроса к API с query параметрами
     *
     * @param string $endpoint Endpoint API
     * @param array $data Данные для отправки в body
     * @param array $queryParams Query параметры (например, withValues=true)
     * @return array
     */
    protected function postWithParams(string $endpoint, array $data = [], array $queryParams = []): array
    {
        $url = $this->apiUrl . $endpoint;

        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        $jsonData = !empty($data) ? Json::encode($data) : '';

        $this->logDebug("POST Request: {$url}");
        $this->logDebug("Username: " . (!empty($this->username) ? $this->username : 'NOT SET'));
        if (!empty($jsonData)) {
            $this->logDebug("Data: " . substr($jsonData, 0, 500));
        }

        // Используем cURL для корректной работы с HTTP Digest
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true);

        if (!empty($jsonData)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }

        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception('cURL Error: ' . $error);
        }

        curl_close($ch);

        $this->lastHttpStatus = $httpStatus;

        return $this->parseResponseCurl($response, $httpStatus);
    }

    /**
     * Выполнение PUT запроса к API с query параметрами
     *
     * @param string $endpoint Endpoint API
     * @param array $data Данные для отправки в body
     * @param array $queryParams Query параметры (например, withValues=true)
     * @return array
     */
    protected function putWithParams(string $endpoint, array $data = [], array $queryParams = []): array
    {
        $url = $this->apiUrl . $endpoint;

        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        $jsonData = !empty($data) ? Json::encode($data) : '';

        $this->logDebug("PUT Request: {$url}");
        $this->logDebug("Username: " . (!empty($this->username) ? $this->username : 'NOT SET'));
        if (!empty($jsonData)) {
            $this->logDebug("Data: " . substr($jsonData, 0, 500));
        }

        // Используем cURL для корректной работы с HTTP Digest
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

        if (!empty($jsonData)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }

        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception('cURL Error: ' . $error);
        }

        curl_close($ch);

        $this->lastHttpStatus = $httpStatus;

        return $this->parseResponseCurl($response, $httpStatus);
    }

    /**
     * Выполнение DELETE запроса к API
     *
     * @param string $endpoint Endpoint API
     * @return array
     */
    protected function delete(string $endpoint): array
    {
        $url = $this->apiUrl . $endpoint;

        // Используем cURL для корректной работы с HTTP Digest
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception('cURL Error: ' . $error);
        }

        curl_close($ch);

        $this->lastHttpStatus = $httpStatus;

        return $this->parseResponseCurl($response, $httpStatus);
    }

    /**
     * Парсинг ответа от API
     *
     * @param string|false $response Ответ от API
     * @return array
     */
    protected function parseResponse($response): array
    {
        if ($response === false) {
            $errors = $this->httpClient->getError();
            $errorMessage = 'HTTP Error: ' . implode(', ', $errors);
            $this->logError($errorMessage);
            throw new \Exception($errorMessage);
        }

        $httpStatus = $this->httpClient->getStatus();

        // Логируем для отладки
        $this->logDebug("HTTP Status: {$httpStatus}");
        $this->logDebug("Response length: " . strlen($response));
        $this->logDebug("Response preview: " . substr($response, 0, 500));

        // Проверяем, что ответ не пустой
        if (empty($response)) {
            $errorMessage = 'Empty response from API';
            $this->logError($errorMessage);
            throw new \Exception($errorMessage);
        }

        // Пробуем распарсить JSON
        try {
            $data = Json::decode($response);
        } catch (\Exception $e) {
            $errorMessage = 'Invalid JSON response (Status: ' . $httpStatus . '). Response: ' . substr($response, 0, 1000);
            $this->logError($errorMessage);

            // Если это HTML ошибка - извлекаем текст
            if (strpos($response, '<html') !== false || strpos($response, '<!DOCTYPE') !== false) {
                $errorMessage = 'API returned HTML instead of JSON. This might be an authentication issue or wrong endpoint.';
            }

            throw new \Exception($errorMessage);
        }

        // Проверяем код ответа
        if ($httpStatus >= 400) {
            $errorData = [
                'error' => [
                    'code' => $httpStatus,
                    'message' => $data['message'] ?? $data['error'] ?? 'HTTP Error ' . $httpStatus,
                ],
            ];
            $this->logError("HTTP Error {$httpStatus}: " . json_encode($errorData));
            return $errorData;
        }

        return $data;
    }

    /**
     * Логирование ошибок
     *
     * @param string $message Сообщение
     * @return void
     */
    protected function logError(string $message): void
    {
        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/osmi_card_api.log';
        $date = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$date}] ERROR: {$message}\n", FILE_APPEND);
    }

    /**
     * Логирование для отладки
     *
     * @param string $message Сообщение
     * @return void
     */
    protected function logDebug(string $message): void
    {
        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/osmi_card_api.log';
        $date = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$date}] DEBUG: {$message}\n", FILE_APPEND);
    }

    /**
     * Парсинг ответа от cURL
     *
     * @param string $response Ответ от API
     * @param int $httpStatus HTTP статус
     * @return array
     */
    protected function parseResponseCurl(string $response, int $httpStatus): array
    {
        // Логируем для отладки
        $this->logDebug("HTTP Status: {$httpStatus}");
        $this->logDebug("Response length: " . strlen($response));
        $this->logDebug("Response preview: " . substr($response, 0, 500));

        // Проверяем, что ответ не пустой
        if (empty($response)) {
            $errorMessage = 'Empty response from API (HTTP ' . $httpStatus . ')';
            $this->logError($errorMessage);

            if ($httpStatus === 401) {
                $errorMessage = 'HTTP 401 Unauthorized. Проверьте Username и Password.';
            }

            throw new \Exception($errorMessage);
        }

        // Пробуем распарсить JSON
        try {
            $data = Json::decode($response);
        } catch (\Exception $e) {
            $errorMessage = 'Invalid JSON response (Status: ' . $httpStatus . '). Response: ' . substr($response, 0, 1000);
            $this->logError($errorMessage);

            // Если это HTML ошибка - извлекаем текст
            if (strpos($response, '<html') !== false || strpos($response, '<!DOCTYPE') !== false) {
                $errorMessage = 'API returned HTML instead of JSON. This might be an authentication issue or wrong endpoint.';
            }

            throw new \Exception($errorMessage);
        }

        // Проверяем код ответа
        if ($httpStatus >= 400) {
            $errorData = [
                'error' => [
                    'code' => $httpStatus,
                    'message' => $data['message'] ?? $data['error'] ?? 'HTTP Error ' . $httpStatus,
                ],
            ];
            $this->logError("HTTP Error {$httpStatus}: " . json_encode($errorData));
            return $errorData;
        }

        return $data;
    }

    /**
     * Проверка настроек API
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->username) && !empty($this->password) && !empty($this->templateId);
    }

    /**
     * Получить template ID из настроек
     *
     * @return string
     */
    public function getTemplateId(): string
    {
        return $this->templateId;
    }

    /**
     * Получение списка шаблонов
     * GET /templates
     *
     * @return array
     */
    public function getTemplates(): array
    {
        try {
            $response = $this->get('/templates');

            if (isset($response['error']) || isset($response['errors'])) {
                return [
                    'success' => false,
                    'error' => $response['error'] ?? $response['message'] ?? 'Неизвестная ошибка',
                ];
            }

            return [
                'success' => true,
                'data' => $response['data'] ?? $response,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

