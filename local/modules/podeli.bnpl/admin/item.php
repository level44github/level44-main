<?php

use \Bitrix\Main\Localization\Loc;

define("STOP_STATISTICS", true);
define("BX_SECURITY_SHOW_MESSAGE", true);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("podeli.bnpl");
if ($saleModulePermissions == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

CModule::IncludeModule("podeli.bnpl");

$requestId = $_GET['id'];
$wrapper = new \Podeli\Bnpl\ClientWrapper();
$items = $wrapper->getOrderItems($requestId);
$items = $items['items'];
if (!$items) {
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
    ShowError(Loc::GetMessage("PODELI.PAYMENT_ORDER_NOT_FOUND"));
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
    die();
}
$transaction = $wrapper->getTransactionList(['ID' => $requestId])->fetch();
$prepaidSum = $wrapper->calcPrepaidAmount($transaction['AMOUNT'], $items);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form method="POST" id="podeli_bnpl_refund_form" action="">
    <?php echo bitrix_sessid_post(); ?>
    <div class="adm-detail-title-view-tab"><?php echo Loc::getMessage('PODELI.PAYMENT_SELECT_REFUND_POSITION'); ?></div>
    <div class="adm-list-table-wrap adm-list-table-without-header adm-list-table-without-footer">
        <table class="adm-list-table">
            <thead>
                <tr class="adm-list-table-header">
                    <td class="adm-list-table-cell"></td>
                    <td class="adm-list-table-cell">
                        <div class="adm-list-table-cell-inner">
                            <strong><?php echo Loc::getMessage('PODELI.PAYMENT_REFUND_POSITION_NAME'); ?></strong>
                        </div>
                    </td>
                    <td class="adm-list-table-cell">
                        <div class="adm-list-table-cell-inner">
                            <strong><?php echo Loc::getMessage('PODELI.PAYMENT_REFUND_POSITION_QUANTITY'); ?></strong>
                        </div>
                    </td>
                    <td class="adm-list-table-cell">
                        <div class="adm-list-table-cell-inner">
                            <strong><?php echo Loc::getMessage('PODELI.PAYMENT_REFUND_POSITION_PRICE'); ?></strong>
                        </div>
                    </td>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $id => $item) : ?>
                    <tr class="adm-list-table-row">
                        <td class="adm-list-table-cell">
                            <input class="podeli_bnpl_refund_position" id="podeli_bnpl_refund_position_<?php echo $id; ?>" type="checkbox" name="position[]" value="<?php echo $id; ?>" checked>
                        </td>
                        <td class="adm-list-table-cell">
                            <label for="podeli_bnpl_refund_position_<?php echo $id; ?>"><?php echo htmlspecialcharsbx($item['name']); ?></label>
                        </td>
                        <td class="adm-list-table-cell">
                            <input size="7" id="podeli_bnpl_refund_quantity_<?php echo $id; ?>" class="podeli_bnpl_refund_quantity" type="text" name="quantity[<?php echo $id; ?>]" value="<?php echo htmlspecialcharsbx($item['quantity']); ?>">
                        </td>
                        <td class="adm-list-table-cell adm-list-table-cell-last" align="right">
                            <input size="7" id="podeli_bnpl_refund_amount_<?php echo $id; ?>" class="podeli_bnpl_refund_amount" readonly type="text" name="amount[<?php echo $id; ?>]" value="<?php echo htmlspecialcharsbx($item['amount']); ?>">
                        </td>
                        <input type="hidden" name="name[<?php echo $id; ?>]" value="<?php echo htmlspecialcharsbx($item['name']); ?>">
                        <input type="hidden" name="article[<?php echo $id; ?>]" value="<?php echo htmlspecialcharsbx(@$item['article']); ?>">
                        <input type="hidden" name="item_id[<?php echo $id; ?>]" value="<?php echo htmlspecialcharsbx(@$item['id']); ?>">
                    </tr>
                <?php endforeach; ?>
                <tr class="adm-list-table-row">
                    <td class="adm-list-table-cell" colspan="3"><?php echo Loc::getMessage('PODELI.PAYMENT_REFUND_SUM'); ?></td>
                    <td class="adm-list-table-cell adm-list-table-cell-last" align="right" id="podeli_bnpl_total_refund_amount">0</td>
                </tr>
            </tbody>
        </table>
    </div>
    <input type="hidden" name="action" value="refund">
    <input type="hidden" name="id" value="<?php echo htmlspecialcharsbx($requestId); ?>">
</form>
<script>
    BX.ready(function() {
        podeliBnplUpdateTotal();
        document.querySelectorAll('.podeli_bnpl_refund_position').forEach(function(el) {
            BX.bind(el, 'change', podeliBnplUpdateTotal);
        });
        document.querySelectorAll('.podeli_bnpl_refund_quantity').forEach(function(el) {
            BX.bind(el, 'keyup', podeliBnplUpdateTotal);
        });
        document.querySelectorAll('.podeli_bnpl_refund_amount').forEach(function(el) {
            BX.bind(el, 'keyup', podeliBnplUpdateTotal);
        });
    });
</script>
<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
