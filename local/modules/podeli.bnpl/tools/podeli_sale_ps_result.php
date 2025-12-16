<?php

use \Bitrix\Main\Application;
use \Bitrix\Sale\PaySystem;
use \Bitrix\Main\IO\File;
use \Bitrix\Main\Request;

define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);
define('NOT_CHECK_PERMISSIONS', true);
define("DisableEventsCheck", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

global $APPLICATION;

function searchByRequest(Request $request) {
    $documentRoot = Application::getDocumentRoot();
    $items = PaySystem\Manager::getList([
        'select' => ['*'],
        'filter' => [
            'ACTIVE' => 'Y',
            'ACTION_FILE' => 'podeli',
        ],
    ]);
    while ($item = $items->fetch()) {
        $name = $item['ACTION_FILE'];
        foreach (PaySystem\Manager::getHandlerDirectories() as $type => $path) {
            $className = '';
            if (File::isFileExists($documentRoot . $path . $name . '/handler.php')) {
                $className = PaySystem\Manager::getClassNameFromPath($item['ACTION_FILE']);
                if (!class_exists($className)) {
                    require_once($documentRoot . $path . $name . '/handler.php');
                }
            }
            if (class_exists($className) && is_callable(array($className, 'isMyResponse'))) {
                if ($className::isMyResponse($request, $item['ID'])) {
                    return $item;
                }
            }
        }
    }
    return false;
}

if (CModule::IncludeModule("sale")) {
    $context = Application::getInstance()->getContext();
    $request = $context->getRequest();
    $item = searchByRequest($request);
    if ($item !== false) {
        $service = new PaySystem\Service($item);
        if ($service instanceof PaySystem\Service) {
            $result = $service->processRequest($request);
        }
    }
    else {
        $debugInfo = http_build_query($request->toArray(), "", "\n");
        if (empty($debugInfo)) {
            $debugInfo = file_get_contents('php://input');
        }
        PaySystem\Logger::addDebugInfo('Pay system not found. Request: '.($debugInfo ? $debugInfo : "empty"));
    }
}

$APPLICATION->FinalActions();
die();
