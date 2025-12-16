<?php

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$request = Application::getInstance()->getContext()->getRequest();
$proto = $request->isHttps() ? 'https' : 'http';

$data = [
    'NAME' => Loc::getMessage('PODELI.PAYMENT_PAYMENT_TITLE'),
    'SORT' => 500,
    'CODES' => [
        'ORDER_NUMBER' => [
            'NAME' => Loc::getMessage('PODELI.PAYMENT_OPTIONS_ORDER_NUMBER'),
            'SORT' => 100,
            'DEFAULT' => [
                'PROVIDER_VALUE' => 'ORDER_NUMBER',
                'PROVIDER_KEY'   => 'ORDER',
            ],
        ],
        'SHOP_NAME' => [
            'NAME' => Loc::getMessage('PODELI.PAYMENT_OPTIONS_SHOP_NAME'),
            'SORT' => 200,
        ],
        'SHOP_PASSWORD' => [
            'NAME' => Loc::getMessage('PODELI.PAYMENT_OPTIONS_SHOP_PASSWORD'),
            'SORT' => 300,
        ],
        'CERT_PATH' => [
            'NAME' => Loc::getMessage('PODELI.PAYMENT_OPTIONS_CERT_PATH'),
            "DESCRIPTION" => Loc::getMessage("PODELI.PAYMENT_OPTIONS_CERT_PATH_DESC"),
            'SORT' => 400,
        ],
        'KEY_PATH' => [
            'NAME' => Loc::getMessage('PODELI.PAYMENT_OPTIONS_KEY_PATH'),
            "DESCRIPTION" => Loc::getMessage("PODELI.PAYMENT_OPTIONS_KEY_PATH_DESC"),
            'SORT' => 430,
        ],
        'CERT_INPUT' => [
            'NAME' => Loc::getMessage('PODELI.PAYMENT_OPTIONS_CERT_INPUT'),
            'DESCRIPTION' => Loc::getMessage('PODELI.PAYMENT_OPTIONS_CERT_INPUT_DESC'),
            'SORT' => 440,
        ],
        'KEY_INPUT' => [
            'NAME' => Loc::getMessage('PODELI.PAYMENT_OPTIONS_KEY_INPUT'),
            'DESCRIPTION' => Loc::getMessage('PODELI.PAYMENT_OPTIONS_KEY_INPUT_DESC'),
            'SORT' => 450,
        ],
        'SUCCESS_URL' => [
            'NAME' => Loc::getMessage('PODELI.PAYMENT_OPTIONS_SUCCESS_URL'),
            "DESCRIPTION" => Loc::getMessage("PODELI.PAYMENT_OPTIONS_SUCCESS_URL_DESC"),
            'SORT' => 500,
            'DEFAULT' => [
                'PROVIDER_KEY' => 'VALUE',
                'PROVIDER_VALUE' => $proto . '://' . $request->getHttpHost() . '/bitrix/tools/sale_ps_success.php',
            ],
        ],
        'FAIL_URL' => [
            'NAME' => Loc::getMessage('PODELI.PAYMENT_OPTIONS_FAIL_URL'),
            "DESCRIPTION" => Loc::getMessage("PODELI.PAYMENT_OPTIONS_FAIL_URL_DESC"),
            'SORT' => 600,
            'DEFAULT' => [
                'PROVIDER_KEY' => 'VALUE',
                'PROVIDER_VALUE' => $proto . '://' . $request->getHttpHost() . '/bitrix/tools/sale_ps_fail.php',
            ],
        ],
        'AUTO_REDIRECT' => [
            'NAME' => Loc::getMessage('PODELI.PAYMENT_OPTIONS_AUTO_REDIRECT'),
            "DESCRIPTION" => Loc::getMessage("PODELI.PAYMENT_OPTIONS_AUTO_REDIRECT_DESC"),
            'SORT' => 700,
            'INPUT' => [
                'TYPE' => 'Y/N',
            ],
            'DEFAULT' => [
                'PROVIDER_KEY' => 'INPUT',
                'PROVIDER_VALUE' => 'Y',
            ],
        ],
        'AUTO_CANCEL' => [
            'NAME' => Loc::getMessage('PODELI.PAYMENT_OPTIONS_AUTO_CANCEL'),
            "DESCRIPTION" => Loc::getMessage("PODELI.PAYMENT_OPTIONS_AUTO_CANCEL_DESC"),
            'SORT' => 800,
            'INPUT' => [
                'TYPE' => 'Y/N',
            ],
            'DEFAULT' => [
                'PROVIDER_KEY' => 'INPUT',
                'PROVIDER_VALUE' => 'N',
            ],
        ],
    ],
];
