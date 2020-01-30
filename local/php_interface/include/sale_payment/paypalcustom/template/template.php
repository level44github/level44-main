<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if ($params["PAYED"] != "Y")
{
?>
    <script>
        $(function () {
            $(".js-pay_form").submit();
        });
    </script>
	<table border="0" width="100%" cellpadding="2" cellspacing="2">
		<tr>
			<td align="center">
				<?
					$itemName = "Invoice ".$params["PAYMENT_ID"]." (".$params["PAYMENT_DATE_INSERT"].")";
				?>
				<form action="<?=$params['URL']?>" method="post" class="js-pay_form">
					<input type="hidden" name="cmd" value="_xclick">
					<input type="hidden" name="buttonsource" value="Bitrix_Cart">
					<input type="hidden" name="business" value="<?= htmlspecialcharsbx($params["PAYPAL_BUSINESS"]) ?>">
					<input type="hidden" name="item_name" value="<?=htmlspecialcharsbx($itemName)?>">
					<input type="hidden" name="currency_code" value="<?=htmlspecialcharsbx($params["PAYMENT_CURRENCY"])?>">
					<input type="hidden" name="amount" value="<?=round($params["PAYMENT_SHOULD_PAY"], 2);?>">
					<input type="hidden" name="custom" value="<?=htmlspecialcharsbx($params["PAYMENT_ID"])?>">

					<?if ($params["PAYPAL_ON0"] != ''):?>
						<input type="hidden" name="on0" value="<?=urlencode($params["PAYPAL_ON0"])?>">
						<input type="hidden" name="os0" value="<?=urlencode($params["PAYPAL_OS0"])?>">
					<?endif;?>

					<?if ($params["PAYPAL_ON1"] != '' && $params["PAYPAL_ON0"] != ''):?>
						<input type="hidden" name="on1" value="<?=urlencode($params["PAYPAL_ON1"])?>">
						<input type="hidden" name="os1" value="<?=urlencode($params["PAYPAL_OS1"])?>">
					<?endif;?>

					<?if ($params["PAYPAL_NOTIFY_URL"] != ''):?>
						<input type="hidden" name="notify_url" value="<?=htmlspecialcharsbx($params["PAYPAL_NOTIFY_URL"])?>">
					<?endif;?>

                    <input type="hidden"
                           name="return"
                           value="https://dev:kuWxuH2t@level44.net<?= SITE_DIR ?>checkout/success/"
                    >
                    <input type="hidden" name="lc" value="<?= \Level44\Base::getMultiLang("RU", "US") ?>">
                </form>
			</td>
		</tr>
	</table>
<?
}
else
{
	echo Loc::getMessage("SALE_HPS_PAYPAL_I3");
}
?>
