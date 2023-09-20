<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$isMain = $APPLICATION->GetCurPage() === SITE_DIR;
\Level44\Base::$typePage = $isMain ? "home" : "";
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
    <? if ($isMain): ?>
    <div class="home">
        <style type="text/css">
            .home {
                margin-top: 50px;
                background-image: url("<?=\Level44\Base::getMainBanner(true)?>");
            }

            @media (min-width: 768px) {
                .home {
                    margin-top: 0;
                    background-image: url("<?=\Level44\Base::getMainBanner()?>");
                }
            }
        </style>
        <? endif; ?>
        <header class="header">
            <div class="container px-lg-1">
                <nav class="navbar layout__navbar navbar-light">
                    <button class="nav-trigger__btn" type="button" aria-label="Toggle navigation">
                        <span class="nav-trigger__icon"></span>
                    </button>
                    <a class="navbar-brand" href="<?= SITE_DIR ?>">LEVEL44</a>
                    <ul class="nav menu ml-auto">
	                    <? $APPLICATION->IncludeComponent(
		                    "bitrix:main.site.selector",
		                    "main",
		                    [
			                    "SITE_LIST"  => ["*all*"],
			                    "CACHE_TYPE" => "A",
			                    "CACHE_TIME" => "3600",
		                    ]
	                    ); ?>
	                    <li class="nav-item m-search js-m-search">
		                    <div class="m-search__container">
			                    <form action="<?= SITE_DIR ?>search" class="js-search__line">
				                    <div class="input-group m-search__group">
					                    <input class="form-control m-search__control js-m-search__control"
					                           type="text"
					                           placeholder="<?= Loc::getMessage("HEADER_SEARCH_ON_SITE") ?>"
					                           name="q"
					                           value="<?= $searchQuery ?>"
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
		                    </div>
	                    </li>
                        <li class="nav-item dropdown m-basket">
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
        <a class="btn btn-outline-light btn__fix-width btn-catalog" href="<?= SITE_DIR ?>catalog/"><?=Loc::getMessage("HEADER_GO_CATALOG")?></a>
    </div>
<? endif; ?>
    <div class="container <? $APPLICATION->ShowViewContent("type-page"); ?>__container">
