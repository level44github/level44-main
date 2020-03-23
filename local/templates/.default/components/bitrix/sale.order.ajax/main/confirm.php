<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<script>
    $(function () {
        var $yaMoneyForm = $("form[name='yandexapi_form']");
        if ($yaMoneyForm.length) {
            var siteDir = '<?=SITE_DIR?>';
            var orderId = '<?=$arResult["ORDER"]["ID"]?>';
            var successUrl = $yaMoneyForm.find("input[name='successURL']").val();
            successUrl = successUrl.replace("/personal/order/", siteDir + "checkout/success/?ps=ym&orderId=" + orderId);
            $yaMoneyForm.find("input[name='successURL']").val(successUrl);
            $yaMoneyForm.submit();
        }

        if ($(".js-ya_cashier_link").length) {
            $(".js-ya_cashier_link")[0].click();
        }
    })
</script>
<?
use Bitrix\Main\Localization\Loc;
\Level44\Base::$typePage = "thank-order";
if (!empty($arResult["ORDER"])):?>
    <? if (!empty($arResult["PAY_SYSTEM"])): ?>
        <? if ($arResult["IS_CASH"]): ?>
            <h1 class="thank-order__title"><?= Loc::getMessage("THANK_ORGER") ?></h1>
            <div class="thank-order__desc"><?= Loc::getMessage("SUC_MESS1") ?></div>
            <a class="btn btn-dark btn__fix-width" href="<?= SITE_DIR ?>"><?= Loc::getMessage("TO_MAIN") ?></a>
        <? endif; ?>
        <? if (strlen($arResult["PAY_SYSTEM"]["ACTION_FILE"]) > 0): ?>
            <? if (!$arResult["IS_CASH"]): ?>
                <h1 class="thank-order__title"><?= Loc::getMessage("WAIT_PAYMENT") ?></h1>
                <div class="thank-order__desc"><?= Loc::getMessage("WAIT_ERROR") ?></div>
            <? endif; ?>
            <? if ($arResult["PAY_SYSTEM"]["NEW_WINDOW"] == "Y"): ?>
                <script language="JavaScript">
                    window.open('<?=$arParams["PATH_TO_PAYMENT"]?>?ORDER_ID=<?=urlencode(urlencode($arResult["ORDER"]["ACCOUNT_NUMBER"]))?>');
                </script>
                <?= GetMessage("SOA_TEMPL_PAY_LINK",
                    Array("#LINK#" => $arParams["PATH_TO_PAYMENT"] . "?ORDER_ID=" . urlencode(urlencode($arResult["ORDER"]["ACCOUNT_NUMBER"])))) ?>
                <?
                if (CSalePdf::isPdfAvailable() && CSalePaySystemsHelper::isPSActionAffordPdf($arResult['PAY_SYSTEM']['ACTION_FILE'])) {
                    ?><br/>
                    <?= GetMessage("SOA_TEMPL_PAY_PDF",
                        Array("#LINK#" => $arParams["PATH_TO_PAYMENT"] . "?ORDER_ID=" . urlencode(urlencode($arResult["ORDER"]["ACCOUNT_NUMBER"])) . "&pdf=1&DOWNLOAD=Y")) ?>
                    <?
                } ?>
            <? else: ?>
                <? if (strlen($arResult["PAY_SYSTEM"]["PATH_TO_ACTION"]) > 0): ?>
                    <? include($arResult["PAY_SYSTEM"]["PATH_TO_ACTION"]); ?>
                <? elseif ($arResult["PAY_SYSTEM"]["BUFFERED_OUTPUT"]): ?>
                    <? echo $arResult["PAY_SYSTEM"]["BUFFERED_OUTPUT"] ?>
                <? endif; ?>
            <? endif; ?>
        <? endif ?>
    <? elseif ($arResult["ORDER"]["PAYED"] === "Y"): ?>
        <h1 class="thank-order__title"><?= Loc::getMessage("ALREADY_PAYED") ?></h1>
        <a class="btn btn-dark btn__fix-width" href="<?= SITE_DIR ?>"><?= Loc::getMessage("TO_MAIN") ?></a>
    <? else: ?>
        <h1 class="thank-order__title"><?= Loc::getMessage("ERROR_ORDER") ?></h1>
        <div class="thank-order__desc"><?= Loc::getMessage("ERROR_MESS") ?></div>
        <a class="btn btn-dark btn__fix-width" href="<?= SITE_DIR ?>"><?= Loc::getMessage("TO_MAIN") ?></a>
    <? endif; ?>

<? else:?>

    <h1 class="thank-order__title"><?= Loc::getMessage("ORDER_NOT_FOUND") ?></h1>
    <div class="thank-order__desc"><?= Loc::getMessage("ERROR_MESS2") ?></div>
    <a class="btn btn-dark btn__fix-width" href="<?= SITE_DIR ?>"><?= Loc::getMessage("TO_MAIN") ?></a>
<? endif; ?>
