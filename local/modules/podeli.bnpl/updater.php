<?php

$MODULE_ID = "podeli.bnpl";
$PAYMENT_NAME = "podeli";
$OLD_TABLE_NAME = "podeli_payment_request";
$TABLE_NAME = "podeli_bnpl_request";

if (IsModuleInstalled('{MODULE_ID}')) {
  if (is_dir(dirname(__FILE__) . '/lang'))
    $updater->CopyFiles("lang", "modules/$MODULE_ID/lang/");
  if (is_dir(dirname(__FILE__) . '/install/js'))
    $updater->CopyFiles("install/js", "js/$MODULE_ID/");
  if (is_dir(dirname(__FILE__) . '/install/admin/js'))
    $updater->CopyFiles("install/admin/js", "js/$MODULE_ID/admin/");
  if (is_dir(dirname(__FILE__) . '/tools'))
    $updater->CopyFiles("tools", "tools/");
  if (is_dir(dirname(__FILE__) . "payment/$PAYMENT_NAME"))
    $updater->CopyFiles("payment/$PAYMENT_NAME", "php_interface/include/sale_payment/$PAYMENT_NAME/");
  if (is_dir(dirname(__FILE__) . "payment/$PAYMENT_NAME/template"))
    $updater->CopyFiles("payment/$PAYMENT_NAME/template", "templates/.default/payment/$PAYMENT_NAME/");
  if (is_dir(dirname(__FILE__) . '/install/logo'))
    $updater->CopyFiles("install/logo", "images/sale/sale_payments/");
  if (is_dir(dirname(__FILE__) . '/install/themes/.default'))
    $updater->CopyFiles("install/themes/.default", "themes/.default/");
  if (is_dir(dirname(__FILE__) . '/install/css'))
    $updater->CopyFiles("install/css", "css/$MODULE_ID/");
  if (is_dir(dirname(__FILE__) . '/install/images'))
    $updater->CopyFiles("install/images", "images/$MODULE_ID/");
  if (is_dir(dirname(__FILE__) . '/install/fonts'))
    $updater->CopyFiles("install/fonts", "fonts/$MODULE_ID/");
}
if ($updater->CanUpdateDatabase()) {
  if ($updater->TableExists("api_orderstatus_history")) {
    global $DB;
    if (!$DB->Query("SELECT `FILES` FROM `api_orderstatus_history` WHERE 1=0", true)) {
      $updater->Query(array(
        "MySQL" => "ALTER TABLE `api_orderstatus_history` ADD `FILES` CHAR(1) NOT NULL DEFAULT 'N' AFTER `MAIL`",
      ));
    }
  }
}

// lang >> bitrix/modules/podeli.bnpl/lang
// install/js >> bitrix/js/podeli.bnpl/
// install/admin/js >> bitrix/js/podeli.bnpl/admin
// tools >> bitrix/tools
// payment/podeli >> bitrix/php_interface/include/sale_payment/podeli
// payment/podeli/template >> bitrix/templates/.default/payment/podeli 
// install/logo >> bitrix/images/sale/sale_payments
// install/themes/.default >> bitrix/themes/.default
// install/css >> bitrix/css/podeli.bnpl 
// install/images >> bitrix/images/podeli.bnpl
// install/fonts >> bitrix/fonts
