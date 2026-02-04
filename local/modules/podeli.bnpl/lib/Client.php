<?php

namespace Podeli\Bnpl;

use Bitrix\Main\Config\Option;

class Client
{
    protected $devUrl = 'https://api-sand.podeli.ru/partners/v1/orders/';
    protected $prodUrl = 'https://api.podeli.ru/partners/v1/orders/';
    protected $url = '';

    protected $login;
    protected $password;
    protected $certPath;
    protected $keyPath;
    protected $certInput;
    protected $keyInput;
    protected $logger;
    protected $debug;
    protected $useCurlRequestHandler;

    public function __construct(
        $login,
        $password,
        $certPath,
        $keyPath,
        $certInput,
        $keyInput,
        $logger = null,
        $isDebug = false,
        $useCurlRequestHandler = true
    ) {
        $this->login = $login;
        $this->password = $password;
        $this->certPath = $certPath;
        $this->keyPath = $keyPath;
        $this->certInput = $certInput;
        $this->keyInput = $keyInput;
        $this->logger = $logger;
        $this->url = $isDebug ? $this->devUrl : $this->prodUrl;
        $this->debug = $isDebug;
        $this->useCurlRequestHandler = $useCurlRequestHandler;
    }

    public static function logToFile($data)
    {
        $writeLog = Option::get('podeli.bnpl', 'write_log');
        if (!$writeLog) return;
        $data = PHP_EOL . PHP_EOL . '[' . date('d/m/Y h:i:s', time()) . ']' .
            PHP_EOL . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/podeli_api.txt', $data, FILE_APPEND | LOCK_EX);
    }

    public function create($data, $correlation = '')
    {
        return $this->execute(
            'POST',
            'create',
            $data,
            $correlation
        );
    }

    public function info($orderId, $correlation = '')
    {
        return $this->execute(
            'GET',
            "$orderId/info",
            [],
            $correlation
        );
    }

    public function cancel($orderId, $data, $correlation = '')
    {
        return $this->execute(
            'POST',
            "$orderId/cancel",
            $data,
            $correlation
        );
    }

    public function refund($orderId, $data, $correlation = '')
    {
        return $this->execute(
            'POST',
            "$orderId/refund",
            $data,
            $correlation
        );
    }

    public function commit($orderId, $data, $correlation = '')
    {
        return $this->execute(
            'POST',
            "$orderId/commit",
            $data,
            $correlation
        );
    }

    protected function curlRequestHandler($method, $action, $data, $correlation)
    {
        $headers = [
            "Content-Type: application/json",
            "X-Correlation-ID: $correlation",
            "Authorization: Basic " . base64_encode("{$this->login}:{$this->password}")
        ];
        $responseHeaders = '';
        if (!function_exists('curl_init')) {
            self::logToFile([
                'url' => $this->url . $action,
                'headers' => $headers,
                'request' => $data,
                'response_headers' => $responseHeaders,
                'Exception' => 'No curl'
            ]);
            throw new \Exception('No curl');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . $action);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $header) use (&$responseHeaders) {
            $responseHeaders .= $header;
            return strlen($header);
        });
        list($this->certPath, $this->keyPath) = ClientUtils::checkCertificateAndKey(
            $this->certPath,
            $this->certInput,
            $this->keyPath,
            $this->keyInput,
            $this->url . $action,
            $headers,
            $data
        );
        curl_setopt($ch, CURLOPT_SSLCERT, $this->certPath);
        curl_setopt($ch, CURLOPT_SSLKEY, $this->keyPath);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $encodedData = '';
        if (!empty($data) || $method == 'POST') {
            $encodedData = ClientUtils::encode($data);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
        }
        $out = curl_exec($ch);
        $curlError = curl_error($ch);
        if ($curlError) {
            self::logToFile([
                'url' => $this->url . $action,
                'headers' => $headers,
                'request' => $data,
                'response_headers' => $responseHeaders,
                'Exception' => $curlError
            ]);
            throw new \Exception($curlError);
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($this->logger) {
            $this->logger->info($method . ' ' . $action);
            $this->logger->info('request' . ' = ' . json_encode($encodedData));
            $this->logger->info('response' . ' = ' . $code . ':' . $out);
        }
        $response = json_decode($out, true);
        self::logToFile([
            'url' => $this->url . $action,
            'headers' => $headers,
            'status' => $code,
            'request' => $data,
            'response_headers' => $responseHeaders,
            'response' => $response
        ]);
        if ($code == 200) {
            return $response;
        } elseif ($code == 429) {
            $headers = ClientUtils::parseHeadersToArray($responseHeaders);
            sleep($headers['X-Retry-After']);
            return $this->execute($action, $data, $method, $correlation);
        }
        $error = 'Error: ' . $code;
        if (isset($response['type']) && $response['type'] == 'error') {
            $error .= ' ' . $response['description'];
        }
        if (isset($response['message'])) {
            $error .= ' ' . $response['message'];
        }
        if (!empty($response['details'])) {
            $list = array_map(
                function ($key, $value) {
                    return "$key - $value";
                },
                array_keys($response['details']),
                array_values($response['details'])
            );
            $error .= ': ' . implode($list);
        }
        if (!$response) {
            $error .= $out;
        }
        throw new \Exception($error, $code);
    }

    protected function fileRequestHandler($method, $action, $data, $correlation)
    {
        $headers = [
            "Content-Type: application/json",
            "X-Correlation-ID: $correlation",
            "Authorization: Basic " . base64_encode("{$this->login}:{$this->password}")
        ];
        $streamOptions = [
            'http' => [
                'method'  => "GET",
                'header'  => implode("\r\n", $headers),
                'ignore_errors' => true
            ],
        ];
        list($this->certPath, $this->keyPath) = ClientUtils::checkCertificateAndKey(
            $this->certPath,
            $this->certInput,
            $this->keyPath,
            $this->keyInput,
            $this->url . $action,
            $headers,
            $data
        );
        $streamOptions['ssl'] = [
            'verify_peer' => true,
            'local_cert' => $this->certPath,
            'local_pk' => $this->keyPath,
        ];
        $encodedData = '';
        if (!empty($data) || $method == 'POST') {
            $encodedData = ClientUtils::encode($data);
            $streamOptions['http']['method'] = 'POST';
            $streamOptions['http']['content'] = $encodedData;
        }
        $context = stream_context_create($streamOptions);
        $url = $this->url . $action;
        $out = file_get_contents($url, false, $context);
        $statusLine = $http_response_header[0];
        preg_match('{HTTP\/\S*\s(\d{3})}', $statusLine, $match);
        $code = $match[1];
        if ($this->logger) {
            $this->logger->info($method . ' ' . $action);
            $this->logger->info('request' . ' = ' . json_encode($encodedData));
            $this->logger->info('response' . ' = ' . $code . ':' . $out);
        }
        $response = json_decode($out, true);
        self::logToFile([
            'url' => $this->url . $action,
            'headers' => $headers,
            'status' => $code,
            'request' => $data,
            'response' => $response
        ]);
        if ($code == 200) {
            return $response;
        } elseif ($code == 429) {
            $headers = ClientUtils::parseHeadersToArray(implode("\r\n", $http_response_header));
            sleep($headers['X-Retry-After']);
            return $this->execute($method, $action, $data, $correlation);
        }
        $error = 'Error: ' . $code;
        if (isset($response['type']) && $response['type'] == 'error') {
            $error .= ' ' . $response['description'];
        }
        if (isset($response['message'])) {
            $error .= ' ' . $response['message'];
        }
        if (!empty($response['details'])) {
            $list = array_map(
                function ($key, $value) {
                    return "$key - $value";
                },
                array_keys($response['details']),
                array_values($response['details'])
            );
            $error .= ': ' . implode($list);
        }
        if (!$response) {
            $error .= $out;
        }
        throw new \Exception($error, $code);
    }

    protected function execute($method, $action, $data, $correlation = '')
    {
        if ($correlation === '') {
            $correlation = ClientUtils::generateCorrelationId();
        }
        if ($this->useCurlRequestHandler) {
            return $this->curlRequestHandler($method, $action, $data, $correlation);
        }
        return $this->fileRequestHandler($method, $action, $data, $correlation);
    }
}
