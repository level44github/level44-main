<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;

use Bitrix\Main\Localization\Loc;
use Level44\Base;

$isMain = $APPLICATION->GetCurPage() === SITE_DIR;
Base::$typePage = $isMain ? "home" : "";
$searchQuery = (string) \Bitrix\Main\Context::getCurrent()
	->getRequest()
	->getQuery("q");
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
    <meta name="description" content="<?= $APPLICATION->GetProperty("description") ?? ""; ?>">
    <meta name="author" content="">
    <title><? $APPLICATION->ShowTitle() ?></title>
    <?
    $APPLICATION->ShowHead();
    Base::loadAssets();
    ?>
</head>
<body class="layout">
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-59KMXKQ"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<? $APPLICATION->ShowPanel(); ?>
<div class="layout__wrapper">
    <header class="header transparent">
        <nav class="header__container">
            <div class="header__column">
                <button class="btn btn-link menu__link nav-trigger__btn" type="button" aria-label="Toggle navigation">
                    <svg class="icon icon-burger menu__icon">
                        <use xlink:href="#burger"></use>
                    </svg>
                </button>
            </div><a class="header__logo" href="<?= SITE_DIR ?>">LEVEL44</a>
            <div class="header__column right">
                <ul class="menu">
                    <? $APPLICATION->IncludeComponent(
                        "bitrix:main.site.selector",
                        "main",
                        [
                            "SITE_LIST"  => ["*all*"],
                            "CACHE_TYPE" => "A",
                            "CACHE_TIME" => "3600",
                        ]
                    ); ?>
                    <li class="m-search js-m-search">
                        <div class="m-search__container">
                            <form>
                                <div class="input-group m-search__group">
                                    <input class="form-control m-search__control js-m-search__control"
                                           type="text"
                                           placeholder="<?= Loc::getMessage("HEADER_SEARCH_ON_SITE") ?>"
                                           autocomplete="off"
                                           name="q"
                                           value="<?= $searchQuery ?>"
                                    >
                                    <div class="input-group-append">
                                        <button class="btn btn-link menu__link m-search__btn" type="button">
                                            <svg class="icon icon-search menu__icon">
                                                <use xlink:href="#search"></use>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </li>
                    <li class="m-basket">
                        <? $APPLICATION->IncludeComponent(
                            "bitrix:sale.basket.basket.line",
                            "top_basket",
                            Array(
                                "HIDE_ON_BASKET_PAGES" => "Y",
                                "PATH_TO_BASKET" => SITE_DIR . "cart/",
                                "PATH_TO_ORDER" => SITE_DIR . "checkout/",
                                "PATH_TO_PERSONAL" => SITE_DIR . "personal/",
                                "PATH_TO_PROFILE" => SITE_DIR . "personal/",
                                "PATH_TO_REGISTER" => SITE_DIR . "login/",
                                "POSITION_FIXED" => "Y",
                                "POSITION_HORIZONTAL" => "right",
                                "POSITION_VERTICAL" => "top",
                                "SHOW_AUTHOR" => "Y",
                                "SHOW_DELAY" => "N",
                                "SHOW_EMPTY_VALUES" => "Y",
                                "SHOW_IMAGE" => "Y",
                                "SHOW_NOTAVAIL" => "N",
                                "SHOW_NUM_PRODUCTS" => "Y",
                                "SHOW_PERSONAL_LINK" => "N",
                                "SHOW_PRICE" => "Y",
                                "SHOW_PRODUCTS" => "Y",
                                "SHOW_SUMMARY" => "Y",
                                "SHOW_TOTAL_PRICE" => "Y"
                            )
                        ); ?>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <? $APPLICATION->IncludeComponent(
        "bitrix:menu",
        "sections_left",
        Array(
            "ROOT_MENU_TYPE" => "left",
            "MAX_LEVEL" => "1",
            "CHILD_MENU_TYPE" => "top",
            "USE_EXT" => "Y",
            "DELAY" => "N",
            "ALLOW_MULTI_SELECT" => "Y",
            "MENU_CACHE_TYPE" => "N",
            "MENU_CACHE_TIME" => "3600",
            "MENU_CACHE_USE_GROUPS" => "Y",
            "MENU_CACHE_GET_VARS" => ""
        )
    ); ?>
    <? if ($isMain): ?>
    <div class="home">
        <?
        $mobileBanner = Base::getMainBanner(true);
        $desktopBanner = Base::getMainBanner();
        ?>

        <? if ($mobileBanner['isVideo']): ?>
            <video autoplay muted loop playsinline class="home__banner mobile">
                <source src="<?= $mobileBanner['src'] ?>"/>
            </video>
        <? else: ?>
            <img src="<?= $mobileBanner['src'] ?>" class="home__banner mobile"/>
        <? endif; ?>

        <? if ($desktopBanner['isVideo']): ?>
            <video autoplay muted loop playsinline class="home__banner desktop">
                <source src="<?= $desktopBanner['src'] ?>"/>
            </video>
        <? else: ?>
            <img src="<?= $desktopBanner['src'] ?>" class="home__banner desktop"/>
        <? endif; ?>
    <? endif; ?>
        <? if ($isMain): ?>
        <a class="btn btn-outline-light btn__fix-width btn-catalog" href="<?= SITE_DIR ?>catalog/novinki/"><?=Loc::getMessage("HEADER_GO_CATALOG")?></a>
    </div>
<? endif; ?>
    <div class="container <? $APPLICATION->ShowViewContent("type-page"); ?>__container">
