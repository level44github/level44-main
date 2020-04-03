<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
\Level44\Base::$typePage = "thank-order";
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$orderId = 0;
$status = false;
\Bitrix\Main\Loader::includeModule("sale");

if ($request->getQuery("ps") === "ym") {
    $orderId = $request->getQuery("orderId");
    $order = \Bitrix\Sale\Order::load($orderId);
    $status = $order->getPaymentCollection()->current()->getField("PAID") === "Y";
} else {
    $status = (string)$request->getQuery("st");
    $cm = (string)$request->getQuery("cm");
    $cm = explode(":", $cm);
    $orderId = (int)$cm[1];
    $status = (string)$request->getQuery("st") === "Completed1";
}

if (!$status && $request->getQuery("ps") === "ym") {
    LocalRedirect(SITE_DIR);
}
?>
<? if ($status): ?>
    <h1 class="thank-order__title">Ваш заказ успешно оплачен</h1>
    <div class="thank-order__desc">Наш менеджер свяжется с вами в течение дня, чтобы подтвердить заказ.</div>
    <a class="btn btn-dark btn__fix-width" href="<?= SITE_DIR ?>">Перейти на главную</a>
<? else: ?>
    <h1 class="thank-order__title">Произошла ошибка оплаты</h1>
    <div class="thank-order__desc">Пожалуйста обратитесь к администрации интернет-магазина. (Номер
        заказа: <?= $orderId ?>)
    </div>
    <a class="btn btn-dark btn__fix-width" href="<?= SITE_DIR ?>">Перейти на главную</a>
<? endif; ?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
