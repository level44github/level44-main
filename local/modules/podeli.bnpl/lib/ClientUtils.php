<?php

namespace Podeli\Bnpl;


class ClientUtils
{
    public static function generateCorrelationId()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    public static function encode($data)
    {
        if (is_string($data)) {
            return $data;
        }
        $result = json_encode($data);
        $error = json_last_error();
        if ($error != JSON_ERROR_NONE) {
            throw new \Exception('JSON Error: ' . json_last_error_msg());
        }
        return $result;
    }

    public static function parseHeadersToArray($rawHeaders)
    {
        $lines = explode("\r\n", $rawHeaders);
        $headers = [];
        foreach ($lines as $line) {
            if (strpos($line, ':') === false) {
                continue;
            }
            list($key, $value) = explode(': ', $line);
            $headers[$key] = $value;
        }
        return $headers;
    }

    public static function prepareOrderId($orderId)
    {
        $orderId = str_replace(['/', '#', '?', '|', ' '], ['-'], $orderId);
        return preg_replace("#\p{Cyrillic}#u", "", $orderId);
    }

    public static function fixCertInput($certInput)
    {
        $name = md5($_SERVER['DOCUMENT_ROOT']);
        $fpath = "/tmp/$name";
        $certInput = str_replace(' ', PHP_EOL, trim(str_replace([
            '-----BEGIN CERTIFICATE-----',
            '-----END CERTIFICATE-----'
        ], ['', ''], $certInput)));
        $certInput = '-----BEGIN CERTIFICATE-----'
            . PHP_EOL . $certInput . PHP_EOL
            . '-----END CERTIFICATE-----';
        return file_put_contents($fpath, $certInput) ? $fpath : false;
    }

    public static function fixKeyInput($keyInput)
    {
        $name = md5($_SERVER['DOCUMENT_ROOT']);
        $fpath = "/tmp/.$name";
        $keyInput = str_replace(' ', PHP_EOL, trim(str_replace([
            '-----BEGIN PRIVATE KEY-----',
            '-----END PRIVATE KEY-----'
        ], ['', ''], $keyInput)));
        $keyInput = '-----BEGIN PRIVATE KEY-----'
            . PHP_EOL . $keyInput . PHP_EOL
            . '-----END PRIVATE KEY-----';
        return file_put_contents($fpath, $keyInput) ? $fpath : false;
    }

    public static function checkCertificateAndKey($certPath, $certInput, $keyPath, $keyInput, $actionUrl, $headers, $data)
    {
        $pathsExists = !empty($certPath) && !empty($keyPath);
        $inputsExists = !empty($certInput) && !empty($keyInput);
        if ($pathsExists || $inputsExists) {
            if (!file_exists($certPath)) {
                if (!empty($certInput)) {
                    if ($fpath = self::fixCertInput($certInput)) {
                        $certPath = $fpath;
                    }
                } else {
                    Client::logToFile([
                        'url' => $actionUrl,
                        'headers' => $headers,
                        'request' => $data,
                        'Exception' => 'Cert path did\'t exist: ' . $certPath
                    ]);
                    throw new \Exception('Cert path did\'t exist: ' . $certPath);
                }
            }
            if (!file_exists($keyPath)) {
                if (!empty($keyInput)) {
                    if ($fpath = self::fixKeyInput($keyInput)) {
                        $keyPath = $fpath;
                    }
                } else {
                    Client::logToFile([
                        'url' => $actionUrl,
                        'headers' => $headers,
                        'request' => $data,
                        'Exception' => 'Key path did\'t exist: ' . $keyPath
                    ]);
                    throw new \Exception('Key path did\'t exist: ' . $keyPath);
                }
            }
            if (!is_readable($certPath)) {
                Client::logToFile([
                    'url' => $actionUrl,
                    'headers' => $headers,
                    'request' => $data,
                    'Exception' => 'Can\'t read cert file: ' . $certPath
                ]);
                throw new \Exception('Can\'t read cert file: ' . $certPath);
            }
            if (!is_readable($keyPath)) {
                Client::logToFile([
                    'url' => $actionUrl,
                    'headers' => $headers,
                    'request' => $data,
                    'Exception' => 'Can\'t read key file: ' . $keyPath
                ]);
                throw new \Exception('Can\'t read key file: ' . $keyPath);
            }
        }
        return [$certPath, $keyPath];
    }
}
