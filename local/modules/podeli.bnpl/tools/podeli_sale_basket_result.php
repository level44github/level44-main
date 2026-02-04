<?php

use \Bitrix\Main\Application;
use \Bitrix\Main\Config\Option;
use \Bitrix\Sale\PriceMaths;

define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);
define('NOT_CHECK_PERMISSIONS', true);
define("DisableEventsCheck", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

global $APPLICATION;

$type = isset($_GET['type']) ? $_GET['type'] : null;
header('Content-Type: application/json; charset=utf-8');

if ($type === 'widget') {
    $paySystemId = -1;
    if (CModule::IncludeModule("sale")) {
        $paySystemResult = \Bitrix\Sale\PaySystem\Manager::getList(array(
            'filter' => array(
                'ACTIVE' => 'Y',
                'ACTION_FILE' => 'podeli'
            )
        ));
        while ($paySystem = $paySystemResult->fetch()) {
            $paySystemId = $paySystem['PAY_SYSTEM_ID'];
        }
    }
    $result = [
        'show_widget' => Option::get('podeli.bnpl', 'show_widget'),
        'discount' => Option::get('podeli.bnpl', 'discount'),
        'payment_limits' => [Option::get('podeli.bnpl', 'widget_payment_min_limit'), Option::get('podeli.bnpl', 'widget_payment_max_limit')],
        'cart_widget_theme' => Option::get('podeli.bnpl', 'cart_widget_theme'),
        'short_badge_widget_type' => Option::get('podeli.bnpl', 'short_badge_widget_type'),
        'short_badge_widget_mode' => Option::get('podeli.bnpl', 'short_badge_widget_mode'),
        'show_header_widget' => Option::get('podeli.bnpl', 'show_header_widget'),
        'header_widget_animate' => Option::get('podeli.bnpl', 'header_widget_animate'),
        'header_widget_mode' => Option::get('podeli.bnpl', 'header_widget_mode'),
        'pay_system_id' => (int)$paySystemId
    ];
    echo \Bitrix\Main\Web\Json::encode($result, JSON_UNESCAPED_UNICODE);
} else if ($type === 'basket') {
    if (CModule::IncludeModule("sale")) {
        $basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), \Bitrix\Main\Context::getCurrent()->getSite());
        $context = new \Bitrix\Sale\Discount\Context\Fuser($basket->getFUserId());
        $discounts = \Bitrix\Sale\Discount::buildFromBasket($basket, $context);
        $r = $discounts->calculate();
        $output = [];
        if ($r->isSuccess()) {
            $result = $r->getData();
            if (isset($result['BASKET_ITEMS'])) {
                $r = $basket->applyDiscount($result['BASKET_ITEMS']);
                if ($r->isSuccess()) {
                    $output = [
                        'price' => $basket->getPrice(),
                    ];
                } else {
                    $output = $r->getErrorMessages();
                }
            }
        } else {
            $output = $r->getErrorMessages();
        }
        echo \Bitrix\Main\Web\Json::encode($output, JSON_UNESCAPED_UNICODE);
    }
}

$APPLICATION->FinalActions();
die();
