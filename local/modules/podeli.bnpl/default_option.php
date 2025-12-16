<?php

$podeli_bnpl_default_option = [
    "auto_commit" => true,
    "article_key" => "ARTNUMBER",
    "debug" => true,
    "write_log" => true,
    "remove_refunded_items_from_order" => false,
    "show_widget" => false,
    "uninstall_with_db" => false,
    "discount" => false,
    "widget_payment_min_limit" => "300",
    "widget_payment_max_limit" => "30000",
    "multi_order_request" => false,
    "use_curl_handler" => true,
    "redirect_exclude_list" => "/personal/orders/\n/order/payment/",
    'cart_widget_theme' => 'dark',
    'short_badge_widget_type' => 'mini',
    'short_badge_widget_mode' => 'none',
    'show_header_widget' => true,
    'header_widget_animate' => true,
    'header_widget_mode' => 'none',
];
