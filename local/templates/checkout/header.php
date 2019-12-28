<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$showBasketCart = $APPLICATION->GetCurPage() === SITE_DIR . "checkout/" && empty($request->getQuery("ORDER_ID"));
?>

<!DOCTYPE html>
<html lang="ru-RU">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Checkout page desc">
    <meta name="author" content="">
    <title><? $APPLICATION->ShowTitle() ?></title>
    <?
    $APPLICATION->ShowHead();
    Helper::loadAssets();
    ?>
</head>
<body class="layout">
<? $APPLICATION->ShowPanel(); ?>
<div class="layout__wrapper">
    <header class="header header_divider">
        <div class="container">
            <nav class="navbar navbar-light bg-light layout__navbar">
                <div class="position-relative">
                    <? if ($showBasketCart): ?>
                        <a class="header__basket-link" href="<?= SITE_DIR . "cart" ?>">
                            <svg class="icon icon-arrow-left mr-2">
                                <use xlink:href="#arrow-left"></use>
                            </svg>
                            <span class="header__basket-text">В корзину</span>
                        </a>
                    <? endif; ?>
                </div>
                <a class="navbar-brand mx-auto" href="<?= SITE_DIR ?>">LEVEL44</a>
            </nav>
        </div>
    </header>
    <div class="container layout__container">
