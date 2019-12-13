<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
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
                <div class="position-relative"><a class="header__basket-link" href="#">
                        <svg class="icon icon-arrow-left mr-2">
                            <use xlink:href="#arrow-left"></use>
                        </svg>
                        <span class="header__basket-text">В корзину</span></a></div>
                <a class="navbar-brand mx-auto" href="#">LEVEL44</a>
            </nav>
        </div>
    </header>
    <div class="container layout__container">