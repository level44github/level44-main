<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var CBitrixPersonalOrderListComponent $component */
/** @var array $arParams */

/** @var array $arResult */

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!empty($arResult['ERRORS']['FATAL'])) {
    foreach ($arResult['ERRORS']['FATAL'] as $code => $error) {
        if ($code !== $component::E_NOT_AUTHORIZED)
            ShowError($error);
    }
    $component = $this->__component;
} else {
    if (!empty($arResult['ERRORS']['NONFATAL'])) {
        foreach ($arResult['ERRORS']['NONFATAL'] as $error) {
            ShowError($error);
        }
    }

    if (empty($arResult['ORDERS'])) {
        ?>
        <h3><?= Loc::getMessage('SPOL_TPL_EMPTY_ORDER_LIST') ?></h3>
        <?
    }
    ?>
    <div class="profile profile-orders">
        <div class="profile__title"><?= Loc::getMessage('SPOL_ORDERS_TITLE') ?></div>
        <? if (!empty($arResult['ORDERS'])): ?>
            <div class="orders-list">
                <? foreach ($arResult['ORDERS'] as $key => $order): ?>
                    <a class="order" href="<?= $order["ORDER"]["URL_TO_DETAIL"] ?>">
                        <div class="order-info">
                            <div>
                                <div class="order-status"><?= Loc::getMessage("SPOL_ORDERS_{$order['ORDER']['STATUS_ID']}_STATUS") ?>
                                    <?= $order['ORDER']['DATE_INSERT_FORMATED'] ?>
                                </div>
                                <div class="order-number">
                                    <?= Loc::getMessage('SPOL_ORDER_NUMBER_TEXT', ['#ORDER_NUMBER#' => $order['ORDER']['ACCOUNT_NUMBER']]); ?>
                                </div>
                            </div>
                            <svg class="icon icon-arrow-back order-arrow">
                                <use xlink:href="#arrow-back"></use>
                            </svg>
                        </div>
                        <div class="product-images">
                            <? foreach ($order['BASKET_ITEMS'] as $basketItem): ?>
                                <? if (!empty($basketItem['PICTURE'])): ?>
                                    <img class="product-image" src="<?= $basketItem['PICTURE'] ?>" alt="">
                                <? endif; ?>
                            <? endforeach; ?>
                        </div>
                    </a>
                <? endforeach; ?>
            </div>
        <? endif; ?>
    </div>
    <?
}
