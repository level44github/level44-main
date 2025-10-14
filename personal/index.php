<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

/** @global CMain $APPLICATION */

$APPLICATION->SetTitle("Личный кабинет");
?>

<div class="row">
	<div class="profile-nav-mobile d-md-none">
		<a class="btn btn-link profile-nav-mobile__link" href="<?$APPLICATION->ShowViewContent("personal.back-link")?>">

            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M13 2L4 12L13 22" stroke="#212121" stroke-width="1.2"/>
                <path d="M4 12L21 12" stroke="#212121" stroke-width="1.2"/>
            </svg>

            <div class="text-block">
				<div><?$APPLICATION->ShowViewContent("personal.navigation-title")?></div>
				<div><?$APPLICATION->ShowViewContent("personal.navigation-subtitle")?></div>
			</div>
		</a>
	</div>
    <?
    $arIds = [];
    if (\Bitrix\Main\Loader::includeModule('awelite.favorite')) {
        $objFavCookies = new \Awelite\Favorite\Cookies();
        $arIds = $objFavCookies->getIds();
    }

    $APPLICATION->IncludeComponent(
        "bitrix:menu",
        "personal_desktop",
        Array(
            "ROOT_MENU_TYPE" => "personal",
            "MAX_LEVEL" => "1",
            "CHILD_MENU_TYPE" => "top",
            "USE_EXT" => "N",
            "DELAY" => "N",
            "ALLOW_MULTI_SELECT" => "N",
            "MENU_CACHE_TYPE" => "A",
            "MENU_CACHE_TIME" => "3600",
            "MENU_CACHE_USE_GROUPS" => "Y",
            "MENU_CACHE_GET_VARS" => "",
            "COUNT" => count($arIds)
        )
    ); ?>
	<div class="col-md-10" id="profile-content">
		<?$APPLICATION->IncludeComponent(
			"bitrix:sale.personal.section",
			"main",
			Array(
				"ACCOUNT_PAYMENT_ELIMINATED_PAY_SYSTEMS" => array("0"),
				"ACCOUNT_PAYMENT_PERSON_TYPE" => "1",
				"ACCOUNT_PAYMENT_SELL_SHOW_FIXED_VALUES" => "Y",
				"ACCOUNT_PAYMENT_SELL_TOTAL" => array("100","200","500","1000","5000",""),
				"ACCOUNT_PAYMENT_SELL_USER_INPUT" => "Y",
				"ACTIVE_DATE_FORMAT" => "d.m.Y",
				"CACHE_GROUPS" => "Y",
				"CACHE_TIME" => "3600",
				"CACHE_TYPE" => "A",
				"CHECK_RIGHTS_PRIVATE" => "N",
				"COMPATIBLE_LOCATION_MODE_PROFILE" => "N",
				"CUSTOM_PAGES" => "",
				"CUSTOM_SELECT_PROPS" => array(""),
				"NAV_TEMPLATE" => "",
				"ORDER_HISTORIC_STATUSES" => array("F"),
				"PATH_TO_BASKET" => "/personal/cart",
				"PATH_TO_CATALOG" => "/catalog/",
				"PATH_TO_CONTACT" => "/about/contacts",
				"PATH_TO_PAYMENT" => "/personal/order/payment/",
				"PER_PAGE" => "20",
				"ORDERS_PER_PAGE" => "99999",
				"PROP_1" => array(),
				"PROP_2" => array(),
				"SAVE_IN_SESSION" => "Y",
                "SEF_FOLDER" => SITE_DIR . "personal/",
				"SEF_MODE" => "Y",
				"SEF_URL_TEMPLATES" => array(
					"order_detail"=>"orders/#ID#/",
					"orders"=>"orders/",
					"private"=>"private/",
					"favorites"=>"favorites/",
                    "loyalty"=>"loyalty/",
                    "main"=>"main/",
                    "about"=>"about/",
				),
				"SEND_INFO_PRIVATE" => "N",
				"SET_TITLE" => "Y",
				"SHOW_ACCOUNT_COMPONENT" => "Y",
				"SHOW_ACCOUNT_PAGE" => "Y",
				"SHOW_ACCOUNT_PAY_COMPONENT" => "Y",
				"SHOW_BASKET_PAGE" => "Y",
				"SHOW_CONTACT_PAGE" => "Y",
				"SHOW_ORDER_PAGE" => "Y",
				"SHOW_PRIVATE_PAGE" => "Y",
				"SHOW_PROFILE_PAGE" => "Y",
				"ALLOW_INNER" => "N",
				"ONLY_INNER_FULL" => "N",
				"SHOW_SUBSCRIBE_PAGE" => "Y",
				"USER_PROPERTY_PRIVATE" => array(),
				"USE_AJAX_LOCATIONS_PROFILE" => "N",
                "ORDER_DEFAULT_SORT" => "DATE_INSERT",
			)
		);?>
	</div>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
