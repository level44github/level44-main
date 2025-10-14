<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponent $component */

/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;

global $USER;
if (!$USER->IsAuthorized()) {
    LocalRedirect($arResult['LINK_TO_LOGIN']);
}

if ($arParams["MAIN_CHAIN_NAME"] !== '') {
    $APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}

$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_KMAIN"));

$APPLICATION->AddViewContent("personal.back-link", $arResult['SEF_FOLDER']);
$APPLICATION->AddViewContent("personal.navigation-title", Loc::getMessage('SPS_CHAIN_KMAIN'));
?>

<script>
    BX.addCustomEvent('controllers-aweliteFavoriteSendData:success',
        function (event) {
            $('.favorites-list .grid__item .js-favorite').each((index, item) => {
                if (!$(item).hasClass('is-in-favorites')) {
                    $(item).closest('.grid__item').remove();
                }
            })

            if (event.data?.data?.count) {
                $('.favorite-item.count').text(event.data.data.count);
            }
        });
</script>

<div class="profile favorites-list">
    <div class="profile__title"><?= Loc::getMessage('SPS_CHAIN_KMAIN') ?></div>

    <div class="lk-main-icon-wrap">
        <a class="lk-main-icon-one" href="/personal/private/">
            <svg width="32" height="31" viewBox="0 0 32 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="16" cy="7.04567" r="6.54567" stroke="#212121"/>
                <path d="M10.4077 17.3792H21.5923C24.8662 17.3792 27.8457 19.2704 29.2397 22.2327L29.3325 22.4299C29.5213 22.8312 29.6674 23.2512 29.769 23.6829L30.1802 25.428C30.6157 27.279 29.7218 29.1869 28.021 30.0374C27.4515 30.322 26.8237 30.4709 26.187 30.4709H5.81299C5.17632 30.4709 4.54847 30.322 3.979 30.0374C2.27818 29.1869 1.38429 27.279 1.81982 25.428L2.23096 23.6829C2.33255 23.2512 2.47866 22.8312 2.66748 22.4299L2.76025 22.2327C4.15428 19.2704 7.1338 17.3792 10.4077 17.3792Z" stroke="#212121"/>
            </svg>
            <span>Мои данные</span>
        </a>
        <a class="lk-main-icon-one" href="/personal/favorites/">
            <svg width="23" height="31" viewBox="0 0 23 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1.2002 0.5H21.7998C22.1864 0.5 22.5 0.813597 22.5 1.2002V29.335C22.4999 29.9381 21.7876 30.259 21.3359 29.8594L12.627 22.1484C11.9836 21.5789 11.0164 21.5789 10.373 22.1484L1.66406 29.8594C1.21236 30.259 0.500125 29.9381 0.5 29.335V1.2002C0.5 0.813596 0.813596 0.5 1.2002 0.5Z" stroke="#212121"/>
            </svg>

            <span>Избранное</span>
        </a>
        <a class="lk-main-icon-one" href="/personal/loyalty/">
            <svg width="34" height="34" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M28.8947 8.68408H5.10522C4.16242 8.68408 3.69101 8.68408 3.39812 8.97698C3.10522 9.26987 3.10522 9.74127 3.10522 10.6841V18.8157V30.9999C3.10522 31.9427 3.10522 32.4141 3.39812 32.707C3.69101 32.9999 4.16241 32.9999 5.10522 32.9999H28.8947C29.8375 32.9999 30.3089 32.9999 30.6018 32.707C30.8947 32.4141 30.8947 31.9427 30.8947 30.9999V18.8157V10.6841C30.8947 9.74127 30.8947 9.26987 30.6018 8.97698C30.3089 8.68408 29.8375 8.68408 28.8947 8.68408Z" stroke="#222222"/>
                <path d="M1.36841 20.842L32.6316 20.842" stroke="#222222" stroke-linecap="round"/>
                <path d="M17 8.68408L17 32.1315" stroke="#222222" stroke-linecap="round"/>
                <path d="M17 6.94737L15.0729 4.69908C13.8947 3.32447 12.3954 2.26193 10.708 1.60574L9.30387 1.05968C7.9928 0.549818 6.57898 1.51696 6.57898 2.92369V5.57924C6.57898 6.40406 7.08534 7.14429 7.85409 7.44324L11.0451 8.68421" stroke="#222222" stroke-linecap="round"/>
                <path d="M17 6.94737L18.9271 4.69908C20.1053 3.32447 21.6046 2.26193 23.292 1.60574L24.6961 1.05968C26.0072 0.549818 27.421 1.51696 27.421 2.92369V5.57924C27.421 6.40406 26.9147 7.14429 26.1459 7.44324L22.9549 8.68421" stroke="#222222" stroke-linecap="round"/>
            </svg>


            <span>Баллы</span>
        </a>
        <a class="lk-main-icon-one" href="/personal/orders/">
            <svg width="23" height="32" viewBox="0 0 23 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1.2002 31.5H21.7998C22.1864 31.5 22.5 31.1864 22.5 30.7998V9.5H13.9629C13.1661 9.5 12.4756 8.94672 12.3027 8.16895L10.7441 1.1543L10.5986 0.5H1.2002C0.813596 0.5 0.5 0.813595 0.5 1.2002V30.7998C0.5 31.1864 0.813596 31.5 1.2002 31.5ZM11.8604 1.22266C11.9011 1.27337 11.9469 1.32035 12.001 1.3584L19.0586 6.32129L11.8604 1.22266Z" stroke="#212121"/>
                <line x1="5" y1="12.4" x2="9" y2="12.4" stroke="#212121" stroke-width="1.2"/>
                <line x1="5" y1="17.4" x2="14" y2="17.4" stroke="#212121" stroke-width="1.2"/>
                <line x1="5" y1="22.4" x2="18" y2="22.4" stroke="#212121" stroke-width="1.2"/>
            </svg>


            <span>Заказы</span>
        </a>
    </div>

    <a href="/catalog/" class="lk-go-to-catalog">Перейти в каталог</a>

    <div class="lk-last-view-wrap">
        <h3>Вы недавно смотрели</h3>
        <div class="lk-last-view-list product__related ">
        <?$APPLICATION->IncludeComponent(
            "bitrix:catalog.products.viewed",
            "last-view",
            Array(
                "ACTION_VARIABLE" => "action_cpv",
                "ADDITIONAL_PICT_PROP_2" => "MORE_PHOTO",
                "ADDITIONAL_PICT_PROP_3" => "-",
                "ADD_PROPERTIES_TO_BASKET" => "Y",
                "ADD_TO_BASKET_ACTION" => "BUY",
                "BASKET_URL" => "/personal/basket.php",
                "CACHE_GROUPS" => "Y",
                "CACHE_TIME" => "3600",
                "CACHE_TYPE" => "A",
                "CART_PROPERTIES_2" => array("NEWPRODUCT","NEWPRODUCT,SALELEADER",""),
                "CART_PROPERTIES_3" => array("COLOR_REF","SIZES_SHOES",""),
                "CONVERT_CURRENCY" => "Y",
                "CURRENCY_ID" => "RUB",
                "DATA_LAYER_NAME" => "dataLayer",
                "DEPTH" => "",
                "DISCOUNT_PERCENT_POSITION" => "top-right",
                "ENLARGE_PRODUCT" => "STRICT",
                "ENLARGE_PROP_2" => "NEWPRODUCT",
                "HIDE_NOT_AVAILABLE" => "N",
                "HIDE_NOT_AVAILABLE_OFFERS" => "L",
                "IBLOCK_ID" => "2",
                "IBLOCK_MODE" => "single",
                "IBLOCK_TYPE" => "catalog",
                "LABEL_PROP_2" => array("NEWPRODUCT"),
                "LABEL_PROP_MOBILE_2" => array(),
                "LABEL_PROP_POSITION" => "top-left",
                "MESS_BTN_ADD_TO_BASKET" => "В корзину",
                "MESS_BTN_BUY" => "Купить",
                "MESS_BTN_DETAIL" => "Подробнее",
                "MESS_BTN_SUBSCRIBE" => "Подписаться",
                "MESS_NOT_AVAILABLE" => "Нет в наличии",
                "MESS_RELATIVE_QUANTITY_FEW" => "мало",
                "MESS_RELATIVE_QUANTITY_MANY" => "много",
                "MESS_SHOW_MAX_QUANTITY" => "Наличие",
                "OFFER_TREE_PROPS_3" => array("COLOR_REF","SIZES_SHOES","SIZES_CLOTHES"),
                "PAGE_ELEMENT_COUNT" => "20",
                "PARTIAL_PRODUCT_PROPERTIES" => "N",
                "PRICE_CODE" => array("BASE"),
                "PRICE_VAT_INCLUDE" => "Y",
                "PRODUCT_BLOCKS_ORDER" => "price,props,quantityLimit,sku,quantity,buttons,compare",
                "PRODUCT_ID_VARIABLE" => "id",
                "PRODUCT_PROPS_VARIABLE" => "prop",
                "PRODUCT_QUANTITY_VARIABLE" => "",
                "PRODUCT_ROW_VARIANTS" => "[{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false}]",
                "PRODUCT_SUBSCRIPTION" => "Y",
                "PROPERTY_CODE_2" => array("NEWPRODUCT","SALELEADER","SPECIALOFFER","MANUFACTURER","MATERIAL","COLOR","SALELEADER,SPECIALOFFER,MATERIAL,COLOR,KEYWORDS,BRAND_REF",""),
                "PROPERTY_CODE_3" => array("ARTNUMBER","COLOR_REF","SIZES_SHOES","SIZES_CLOTHES",""),
                "PROPERTY_CODE_MOBILE_2" => array(),
                "RELATIVE_QUANTITY_FACTOR" => "5",
                "SECTION_CODE" => "",
                "SECTION_ELEMENT_CODE" => "",
                "SECTION_ELEMENT_ID" => "",
                "SECTION_ID" => "",
                "SHOW_CLOSE_POPUP" => "N",
                "SHOW_DISCOUNT_PERCENT" => "Y",
                "SHOW_FROM_SECTION" => "N",
                "SHOW_MAX_QUANTITY" => "M",
                "SHOW_OLD_PRICE" => "Y",
                "SHOW_PRICE_COUNT" => "1",
                "SHOW_PRODUCTS_2" => "N",
                "SHOW_SLIDER" => "Y",
                "SLIDER_INTERVAL" => "3000",
                "SLIDER_PROGRESS" => "Y",
                "TEMPLATE_THEME" => "blue",
                "USE_ENHANCED_ECOMMERCE" => "N",
                "USE_PRICE_COUNT" => "N",
                "USE_PRODUCT_QUANTITY" => "Y"
            )
        );?>
        </div>
    </div>

</div>
