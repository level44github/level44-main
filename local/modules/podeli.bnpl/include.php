<?php

CJSCore::RegisterExt('podeli_bnpl_frontend', [
    'js' => '/bitrix/js/podeli.bnpl/script.js',
    'css' => '/bitrix/css/podeli.bnpl/style.css',
    'lang' => '/bitrix/modules/podeli.bnpl/lang/' . LANGUAGE_ID . '/js/script.php',
]);
CJSCore::Init(["podeli_bnpl_frontend"]);

Bitrix\Main\Loader::registerAutoLoadClasses(
    "podeli.bnpl",
    [
        "Podeli\\Bnpl\\Client" => "lib/Client.php",
        "Podeli\\Bnpl\\ClientWrapper" => "lib/ClientWrapper.php",
        "Podeli\\Bnpl\\ClientUtils" => "lib/ClientUtils.php",
        "Podeli\\Bnpl\\Orm\\RequestTable" => "lib/orm/request.php",
    ]
);
