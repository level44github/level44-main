<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @global CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

/** @var string $templateFolder */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Level44\Base;
use Level44\Delivery;

$APPLICATION->SetTitle("");

if (!empty($arResult['ERRORS']['FATAL'])) {
    $component = $this->__component;
    foreach ($arResult['ERRORS']['FATAL'] as $code => $error) {
        if ($code !== $component::E_NOT_AUTHORIZED)
            ShowError($error);
    }
} else {
    if (!empty($arResult['ERRORS']['NONFATAL'])) {
        foreach ($arResult['ERRORS']['NONFATAL'] as $error) {
            ShowError($error);
        }
    }
    ?>
    <div class="profile profile-order">
        <div class="order-header">
            <a class="order-back" href="<?= $arResult['URL_TO_LIST'] ?>">
                <svg class="icon icon-arrow-back order-arrow">
                    <use xlink:href="#arrow-back"></use>
                </svg>
                <span><?= Loc::getMessage('SPOD_ORDER_NUMBER_TEXT', ['#ORDER_NUMBER#' => $arResult["ACCOUNT_NUMBER"]]) ?></span>
                <div class="order-status">
                    <?= Loc::getMessage("SPOD_ORDER_{$arResult["STATUS"]["ID"]}_STATUS") ?> <?= $arResult["DATE_INSERT_FORMATED"] ?>
                </div>
            </a>
            <?
            /*<a class="order-ask" href="#">Вопрос по заказу</a>*/
            ?>
        </div>
        <div class="order-body">
            <div class="order-info">
                <div class="order-info__item">
                    <div class="order-info__item-title"><?= Loc::getMessage('SPOD_ORDER_RECIPIENT') ?></div>
                    <div class="order-info__item-value">
                        <? if (!empty($arResult['FIO'])): ?>
                            <div class="order-info__item-value"><?= $arResult['FIO'] ?></div>
                        <? endif; ?>
                        <? if (!empty($arResult['PROPERTIES']['PHONE']['VALUE'])): ?>
                            <div class="order-info__item-value"><?= $arResult['PROPERTIES']['PHONE']['VALUE'] ?></div>
                        <? endif; ?>
                        <? if (!empty($arResult['PROPERTIES']['EMAIL']['VALUE'])): ?>
                            <div class="order-info__item-value"><?= $arResult['PROPERTIES']['EMAIL']['VALUE'] ?></div>
                        <? endif; ?>
                    </div>
                </div>
                <div class="order-info__item">
                    <div class="order-info__item-title"><?= Loc::getMessage('SPOD_ORDER_DELIVERY_METHOD') ?></div>
                    <div class="order-info__item-value">
                        <? if (!empty($arResult['DELIVERY']['ID'])): ?>
                            <div class="order-info__item-value">
                                <?= Loc::getMessage('SPOD_ORDER_' . Delivery::getType($arResult['DELIVERY']['ID'])?->name . '_DELIVERY_TYPE') ?>
                            </div>
                        <?endif; ?>
                        <? if (!empty($arResult['PROPERTIES']['ADDRESS']['VALUE'])): ?>
                            <div class="order-info__item-value"><?= $arResult['PROPERTIES']['ADDRESS']['VALUE'] ?></div>
                        <? elseif (!empty($arResult['ADDRESS_SDEK'])): ?>
                            <div class="order-info__item-value"><?= $arResult['ADDRESS_SDEK'] ?></div>
                        <? endif; ?>
                    </div>
                </div>
                <div class="order-info__item">
                    <div class="order-info__item-title">
                        <?= $arResult['PAYED'] === 'Y' ? Loc::getMessage('SPOD_ORDER_PAID') : Loc::getMessage('SPOD_ORDER_UNPAID'); ?>
                        <?= Loc::getMessage("SPOD_{$arResult['PAY_SYSTEM']['CODE']}_PAY_SYSTEM_NAME") ?>
                    </div>
                    <div class="order-info__item-value">
                        <div class="order-info__item-value__grid">
                            <div>
                                <?= Base::declOfNum($arResult['BASKET_COUNT'], [
                                    Loc::getMessage('SPOD_ORDER_PRODUCT_SUM_PRODUCT', ['#NUM#' => $arResult['BASKET_COUNT']]),
                                    Loc::getMessage('SPOD_ORDER_PRODUCT_SUM_PRODUCTA', ['#NUM#' => $arResult['BASKET_COUNT']]),
                                    Loc::getMessage('SPOD_ORDER_PRODUCT_SUM_PRODUCTS', ['#NUM#' => $arResult['BASKET_COUNT']])
                                ]) ?>
                            </div>
                            <div><?= $arResult['PRODUCT_SUM_FORMATED'] ?></div>
                        </div>
                        <?
                        /*
                        <div class="order-info__item-value__grid">
                            <div>Списано бонусов</div>
                            <div>1 200 ₽</div>
                        </div>
                        */
                        ?>
                        <div class="order-info__item-value__grid">
                            <div><?= Loc::getMessage('SPOD_ORDER_PRICE') ?></div>
                            <div><?= $arResult['PRICE_FORMATED'] ?></div>
                        </div>
                        <?
                        /*
                        <div class="order-info__item-value__grid">
                            <div>Начислено бонусов</div>
                            <div>3 400 ₽</div>
                        </div>
                        */
                        ?>
                    </div>
                </div>
            </div>
            <? if (!empty($arResult['BASKET'])): ?>
                <div class="order-products">
                    <? foreach ($arResult['BASKET'] as $item): ?>
                        <a class="order-product" href="<?= $item['DETAIL_PAGE_URL'] ?>">
                            <div class="order-product__image">
                                <img class="order-product__image" src="<?= $item['PICTURE'] ?>" alt="">
                            </div>
                            <div class="order-product__info">
                                <div class="order-product__info-name"><?= $item['NAME'] ?></div>
                                <? /*
                        <div class="order-product__info-params">
                            <div class="order-product__info-params-item">
                                <div class="title">Цвет</div>
                                <div class="value">Шоколад</div>
                            </div>
                            <div class="order-product__info-params-item">
                                <div class="title">Размер</div>
                                <div class="value">S</div>
                            </div>
                        </div>
                        */
                                ?>
                                <div class="order-product__info-price">
                                    <div class="price"><?= $item['PRICE_FORMATED'] ?></div>
                                    <? if (!empty($item['oldPrice'])): ?>
                                        <div class="old-price"><?= $item['oldPriceFormat'] ?></div>
                                    <? endif; ?>
                                </div>
                                <? if (!empty($item['PRICE_DOLLAR'])): ?>
                                    <div class="order-product__info-price">
                                        <div class="price"><?= $item['PRICE_DOLLAR_FORMAT'] ?></div>
                                        <? if (!empty($item['oldPriceDollar'])): ?>
                                            <div class="old-price"><?= $item['oldPriceDollarFormat'] ?></div>
                                        <? endif; ?>
                                    </div>
                                <? endif; ?>
                            </div>
                        </a>
                    <? endforeach; ?>
                </div>
            <? endif; ?>
        </div>
    </div>
    <?php
}
