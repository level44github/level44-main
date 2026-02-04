<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if ($APPLICATION->GetGroupRight("podeli.bnpl") != "D") {
    $aMenu = [
        "parent_menu" => "global_menu_store",
        "section" => "podeli_bnpl",
        "sort" => 100,
        "text" => GetMessage("PODELI.PAYMENT_MENU_TITLE"),
        "title" => GetMessage("PODELI.PAYMENT_MENU_TITLE"),
//        "icon" => "podeli_bnpl_menu_icon",
//        "page_icon" => "podeli_bnpl_menu_icon",
        "items_id" => "menu_podeli_bnpl",
        "url" => "podeli.bnpl_list.php?lang=" . LANGUAGE_ID,
    ];
    return $aMenu;
}

return false;
