<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Order;

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/include.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/general/admin_tool.php";

Loc::loadMessages(__FILE__);

\Bitrix\Main\UI\Extension::load("ui.dialogs.messagebox");

$saleModulePermissions = $APPLICATION->GetGroupRight("podeli.bnpl");
if ($saleModulePermissions == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}
ClearVars('l_');

CModule::IncludeModule('podeli.bnpl');
CModule::IncludeModule('sale');
CJSCore::RegisterExt('podeli_bnpl', [
    'js' => '/bitrix/js/podeli.bnpl/admin/script.js',
    'lang' => '/bitrix/modules/podeli.bnpl/lang/' . LANGUAGE_ID . '/admin/js/script.php',
]);

CUtil::InitJSCore(['window', 'podeli_bnpl']);

$arUserGroups = $USER->GetUserGroupArray();
$intUserID = intval($USER->GetID());
$sTableID = "podeli_bnpl_request";

$oSort  = new CAdminSorting($sTableID, "ORDER_ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$wrapper = new \Podeli\Bnpl\ClientWrapper();

if (!empty($_POST) && check_bitrix_sessid()) {
    $result = $wrapper->processAction($_POST);
    if (!$result->isSuccess()) {
        $lAdmin->AddUpdateError(implode(", ", $result->getErrorMessages()));
    }
    if (in_array($_POST['action'], ['commit', 'cancel', 'update'])) {
        $errors = $result->getErrorMessages();
        foreach ($errors as &$err) {
            if (mb_strpos($err, '422') !== false) {
                $err = Loc::getMessage('PODELI.PAYMENT_ERROR_422');
            }
        }
        echo \Bitrix\Main\Web\Json::encode([
            'result' => $result->isSuccess(),
            'errors' => $errors,
        ], JSON_UNESCAPED_UNICODE);
        die;
    }
}

$arFilterFields = array(
    "filter_order_number",
);

$lAdmin->InitFilter($arFilterFields);
$arFilter = array();
if ($filter_order_number != '') {
    $arFilter["order_number"] = $filter_order_number;
}

$arFilterFieldsTmp = array(
    "filter_order_number" => Loc::GetMessage("PODELI.PAYMENT_ORDER_NUMBER"),
);

$oFilter = new CAdminFilter(
    $sTableID . "_filter",
    $arFilterFieldsTmp
);

$arHeaders = [
    ["id" => "ID", "content" => Loc::GetMessage('PODELI.PAYMENT_ORDER_NUMBER'), "sort" => 'ORDER_ID', "default" => true],
    ["id" => "AMOUNT", "content" => Loc::GetMessage('PODELI.PAYMENT_ORDER_SUM'), "sort" => false, "default" => true],
    ["id" => "ITEMS", "content" => Loc::GetMessage('PODELI.PAYMENT_ITEMS'), "sort" => false, "default" => true],
    ["id" => "REFUND", "content" => Loc::GetMessage('PODELI.PAYMENT_ITEMS_REFUND'), "sort" => false, "default" => true],
    ["id" => "SINGLE_PAYMENT", "content" => Loc::GetMessage('PODELI.PAYMENT_SINGLE_PAYMENT'), "sort" => false, "default" => true],
    ["id" => "STATUS", "content" => Loc::GetMessage('PODELI.PAYMENT_STATUS'), "sort" => false, "default" => true],
    ["id" => "CREATED", "content" => Loc::GetMessage('PODELI.PAYMENT_DATE'), "sort" => 'ORDER_ID', "default" => true],
    ["id" => "ACTION", "content" => Loc::GetMessage('PODELI.PAYMENT_ACTION'), "sort" => false, "default" => true],
];

$lAdmin->AddHeaders($arHeaders);

$arFilterOrder = array();
if (!empty($by)) {
    if (!isset($order) || !is_string($order)) {
        $order = "DESC";
    }
    $arFilterOrder[$by] = $order;
}

if ($del_filter == 'Y') {
    $arFilter = [];
}

$dbOrderList = $wrapper->getTransactionList($arFilter, $arFilterOrder);

$dbOrderList = new CAdminResult($dbOrderList, $sTableID);
$dbOrderList->NavStart();

$lAdmin->NavText($dbOrderList->GetNavPrint(""));
while ($arOrder = $dbOrderList->NavNext(true, "f_")) {
    $order = Order::load($arOrder['ORDER_ID']);
    $orderAr = CSaleOrder::GetByID($arOrder['ORDER_ID']);
    $row = &$lAdmin->AddRow($f_ID, $arOrder, "sale_order_view.php?ID=" . $arOrder['ID'] . "&lang=" . LANGUAGE_ID . GetFilterParams("filter_"));
    $idTmp = '<a href="/bitrix/admin/sale_order_view.php?ID=' . $arOrder["ORDER_ID"] . '" title="' . Loc::GetMessage("PODELI.PAYMENT_VIEW_ORDER") . '">' . $arOrder['ORDER_NUMBER'] . '</a>';
    $row->AddField("ID", $idTmp);
    $itemsData = \Bitrix\Main\Web\Json::decode($arOrder['ITEMS']);
    $arItems = [];
    foreach ($itemsData['items'] as $item) {
        $arItems[] = Loc::getMessage('PODELI.PAYMENT_ITEM_INFORMATION', [
            'NAME' => $item['name'],
            'ARTICLE' => $item['article'],
            'AMOUNT' => $item['amount'],
            'QUANTITY' => $item['quantity']
        ]);
    }
    $itemsValue = count($arItems)
        ? implode('<br>', $arItems)
        : Loc::getMessage('PODELI.PAYMENT_NO_ITEMS');
    $row->AddField("ITEMS", $itemsValue);
    $arItemsRefund = [];
    foreach ($itemsData['refund'] as $item) {
        $arItemsRefund[] = Loc::getMessage('PODELI.PAYMENT_ITEM_INFORMATION', [
            'NAME' => $item['name'],
            'ARTICLE' => $item['article'],
            'AMOUNT' => $item['amount'],
            'QUANTITY' => $item['quantity']
        ]);
    }
    $itemsRefundValue = count($arItemsRefund)
        ? implode('<br>', $arItemsRefund)
        : Loc::getMessage('PODELI.PAYMENT_NO_REFUND_ITEMS');
    $row->AddField("REFUND", $itemsRefundValue);
    $paymentScheduleData = \Bitrix\Main\Web\Json::decode($arOrder['PAYMENT_SCHEDULE']);
    $fullPayment = is_array($paymentScheduleData) && count($paymentScheduleData) === 1 ? Loc::getMessage('PODELI.PAYMENT_SINGLE_PAYMENT_YES') : '';
    $row->AddField("SINGLE_PAYMENT", $fullPayment);
    $status = Loc::GetMessage('PODELI.PAYMENT_STATUS_' . strtoupper($arOrder["STATUS"]));
    $row->AddField("STATUS", $status);
    $action = [];
    if (mb_strtolower($arOrder['STATUS']) == 'wait_for_commit') {
        $action[] = '<button class="adm-btn" style="width:100px;" type="button" value="commit" onclick="showPodeliCommitForm(\'' . $arOrder['ID'] . '\')">' . Loc::GetMessage("PODELI.PAYMENT_COMMIT_ACTION") . '</button>';
    }
    if (in_array(mb_strtolower($arOrder["STATUS"]), ["committed", "completed", "refunded"]) && count($arItems)) {
        $action[] = '<button class="adm-btn" style="width:80px;" type="button" value="refund" onclick="showPodeliRefundForm(\'' . $arOrder['ID'] . '\')">' . Loc::GetMessage("PODELI.PAYMENT_REFUND_ACTION") . '</button>';
    }
    if (in_array(mb_strtolower($arOrder["STATUS"]), ["created", "scoring", "approved", "wait_for_commit"])) {
        $action[] = '<button class="adm-btn" style="width:80px;" type="button" value="cancel" onclick="showPodeliCancelForm(\'' . $arOrder['ID'] . '\')">' . Loc::GetMessage("PODELI.PAYMENT_CANCEL_ACTION") . '</button>';
    }
    $action[] = '<button class="adm-btn" style="width:80px;" type="button" value="update" onclick="showPodeliUpdateForm(\'' . $arOrder['ID'] . '\')">' . Loc::GetMessage("PODELI.PAYMENT_UPDATE_ACTION") . '</button>';
    $row->AddField('ACTION', implode('&nbsp;', $action));
}

$lAdmin->CheckListMode();
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/prolog.php";

$APPLICATION->SetTitle(Loc::GetMessage("PODELI.PAYMENT_TRANSACTION_TITLE"));

require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php";
?>
<form name="find_form" method="GET" action="<?php echo $APPLICATION->GetCurPage(); ?>?">
    <?php $oFilter->Begin(); ?>
    <tr>
        <td><?= Loc::GetMessage("PODELI.PAYMENT_ORDER_NUMBER") ?>:</td>
        <td>
            <input type="text" name="filter_order_number" value="<?php echo htmlspecialcharsbx($filter_order_number); ?>" size="40">
        </td>
    </tr>
    <?php
    $oFilter->Buttons([
        "table_id" => $sTableID,
        "url"      => $APPLICATION->GetCurPage(),
        "form"     => "find_form",
    ]);
    $oFilter->End();
    ?>
</form>
<?php
$lAdmin->DisplayList();
require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php";
