<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
use Bitrix\Main\Localization\Loc;

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$showBasketCart = $APPLICATION->GetCurPage() === SITE_DIR . "checkout/" && empty($request->getQuery("ORDER_ID"));
?>

<!DOCTYPE html>
<html lang="ru-RU">
<head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-59KMXKQ');</script>
    <!-- End Google Tag Manager -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Checkout page desc">
    <meta name="author" content="">
    <title><? $APPLICATION->ShowTitle() ?></title>
    <?
    $APPLICATION->ShowHead();
    \Level44\Base::loadAssets();
    ?>
</head>
<body class="layout">
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-59KMXKQ"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<? $APPLICATION->ShowPanel(); ?>
<div class="layout__wrapper">
    <header class="header header_divider">
        <div class="container">
            <nav class="navbar navbar-light bg-light layout__navbar">
                <div class="position-relative">
                    <? if ($showBasketCart): ?>
                        <a class="header__basket-link js-basket-link" href="<?= SITE_DIR . "cart" ?>">
                            <svg class="icon icon-arrow-left mr-2">
                                <use xlink:href="#arrow-left"></use>
                            </svg>
                            <span class="header__basket-text"><?= Loc::getMessage("TO_BASKET") ?></span>
                        </a>
                    <? endif; ?>
                </div>
                <a class="navbar-brand mx-auto" href="<?= SITE_DIR ?>">LEVEL44</a>
            </nav>
        </div>
    </header>
    <div class="container layout__container">
