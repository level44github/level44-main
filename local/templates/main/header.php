<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$isMain = $APPLICATION->GetCurPage() === SITE_DIR;
\Helper::$typePage = $isMain ? "home" : "";
?>
<!DOCTYPE html>
<html lang="ru-RU">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?= $APPLICATION->GetProperty("description") ?? ""; ?>">
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
    <? if ($isMain): ?>
    <div class="home">
        <style type="text/css">
            .home {
                background-image: url("<?=Helper::getAssetsPath()?>/img/home-mobile.jpg");
            }

            @media (min-width: 768px) {
                .home {
                    background-image: url("<?=Helper::getAssetsPath()?>/img/home.jpg");
                }
            }
        </style>
        <? endif; ?>
        <header class="header">
            <div class="container px-lg-1">
                <nav class="navbar layout__navbar navbar-<?= $isMain ? "dark" : "light" ?>">
                    <button class="nav-trigger__btn" type="button" aria-label="Toggle navigation">
                        <span class="nav-trigger__icon"></span>
                    </button>
                    <a class="navbar-brand" href="<?= SITE_DIR ?>">LEVEL44</a>
                    <ul class="nav menu ml-auto">
                        <li class="nav-item m-search js-m-search">
                            <form action="<?= SITE_DIR ?>search">
                                <div class="input-group m-search__group">
                                    <input class="form-control m-search__control js-m-search__control"
                                           type="text"
                                           placeholder="Найти на сайте"
                                           name="q"
                                           autocomplete="off"
                                    >
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary m-search__btn" type="submit">
                                            <svg class="icon icon-search menu__icon">
                                                <use xlink:href="#search"></use>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </li>
                        <li class="nav-item dropdown m-basket">
                            <a class="menu__link" href="#" role="button"
                               data-toggle="dropdown" aria-haspopup="true"
                               aria-expanded="false">
                                <svg class="icon icon-basket menu__icon">
                                    <use xlink:href="#basket"></use>
                                </svg>
                                <div class="menu__basket-count">1</div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right m-basket__dropdown js-m-basket__dropdown">
                                <div class="m-basket__title">Корзина</div>
                                <div class="m-basket__items">
                                    <div class="m-basket__item"><a class="m-basket__image" href="#"><img
                                                    class="img-fluid" src="img/m-basket__item.jpg" alt=""></a>
                                        <div class="m-basket__body"><a href="#">
                                                <div>Платье с v-образным вырезом</div>
                                                <div>14 800 руб.</div>
                                                <div>Размер: S</div>
                                            </a></div>
                                        <a class="m-basket__remove" href="#">
                                            <svg class="icon icon-close ">
                                                <use xlink:href="#close"></use>
                                            </svg>
                                        </a>
                                    </div>
                                    <div class="m-basket__item"><a class="m-basket__image" href="#"><img
                                                    class="img-fluid" src="img/m-basket__item.jpg" alt=""></a>
                                        <div class="m-basket__body"><a href="#">
                                                <div>Платье с v-образным вырезом</div>
                                                <div>14 800 руб.</div>
                                                <div>Размер: S</div>
                                            </a></div>
                                        <a class="m-basket__remove" href="#">
                                            <svg class="icon icon-close ">
                                                <use xlink:href="#close"></use>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                <a class="btn btn-dark btn-block" href="#">Перейти к оформлению заказа</a>
                            </div>
                        </li>
                    </ul>
                </nav>
            </div>
        </header>
        <div class="nav-trigger__body">
            <div class="nav-trigger__header">
                <button class="nav-trigger__btn nav-trigger_body" type="button" aria-label="Toggle navigation">
                    <span class="nav-trigger__icon"></span>
                </button>
            </div>
            <div class="nav-trigger__scroll">
                <? $APPLICATION->IncludeComponent(
                    "bitrix:menu",
                    "sections_left",
                    Array(
                        "ROOT_MENU_TYPE" => "catalog",
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
            </div>
        </div>
        <? if ($isMain): ?>
        <a class="btn btn-outline-light btn__fix-width" href="<?= SITE_DIR ?>catalog/">Перейти в каталог</a>
    </div>
<? endif; ?>
    <div class="container <? $APPLICATION->ShowViewContent("type-page"); ?>__container">
						