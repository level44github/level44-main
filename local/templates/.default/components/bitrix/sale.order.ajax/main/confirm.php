<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<?
\Helper::$typePage = "thank-order";
if (!empty($arResult["ORDER"])):?>
    <? if (!empty($arResult["PAY_SYSTEM"])): ?>
        <? if ($arResult["IS_CASH"]): ?>
            <h1 class="thank-order__title">Спасибо за заказ</h1>
            <div class="thank-order__desc">Наш менеджер свяжется с вами в течении часа, чтобы подтвердить заказ.</div>
            <a class="btn btn-dark btn__fix-width" href="<?= SITE_DIR ?>">Перейти на главную</a>
        <? endif; ?>
        <? if (strlen($arResult["PAY_SYSTEM"]["ACTION_FILE"]) > 0): ?>
            <? if (!$arResult["IS_CASH"]): ?>
                <h1 class="thank-order__title">Ваш заказ оформлен</h1>
                <div class="thank-order__desc">Ожидайте, проиcходит перенаправление на страницу оплаты...</div>
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
        <h1 class="thank-order__title">Заказ уже оплачен</h1>
        <a class="btn btn-dark btn__fix-width" href="<?= SITE_DIR ?>">Перейти на главную</a>
    <? else: ?>
        <h1 class="thank-order__title">Ошибка оформления заказа</h1>
        <div class="thank-order__desc">Пожалуйста обратитесь к администрации интернет-магазина или попробуйте оформить
            ваш
            заказ еще раз.
        </div>
        <a class="btn btn-dark btn__fix-width" href="<?= SITE_DIR ?>">Перейти на главную</a>
    <? endif; ?>

<? else:?>

    <h1 class="thank-order__title">Заказ не найден</h1>
    <div class="thank-order__desc">Пожалуйста обратитесь к администрации интернет-магазина или попробуйте оформить ваш
        заказ еще раз.
    </div>
    <a class="btn btn-dark btn__fix-width" href="<?= SITE_DIR ?>">Перейти на главную</a>
<? endif; ?>
