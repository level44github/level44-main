<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Корзина");
?>

<? $APPLICATION->IncludeComponent(
    "bitrix:sale.basket.basket",
    "main",
    array(
        "COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
        "COLUMNS_LIST" => array(
            0 => "NAME",
            1 => "DISCOUNT",
            2 => "PRICE",
            3 => "QUANTITY",
            4 => "SUM",
            5 => "PROPS",
            6 => "DELETE",
            7 => "DELAY",
        ),
        "AJAX_MODE" => "Y",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "Y",
        "AJAX_OPTION_HISTORY" => "N",
        "PATH_TO_ORDER" => SITE_DIR . "checkout/",
        "HIDE_COUPON" => "N",
        "QUANTITY_FLOAT" => "N",
        "PRICE_VAT_SHOW_VALUE" => "Y",
        "TEMPLATE_THEME" => "site",
        "SET_TITLE" => "Y",
        "SHOW_RESTORE" => "N",
        "AJAX_OPTION_ADDITIONAL" => "",
        "OFFERS_PROPS" => array(
            0 => "SIZES_SHOES",
            1 => "SIZES_CLOTHES",
            2 => "COLOR_REF",
            3 => "NAME_EN",
        ),
    ),
    false
); ?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
