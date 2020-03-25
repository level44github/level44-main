<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
IncludeModuleLangFile(__FILE__);
if(!CModule::IncludeModule("vampirus.yandex")) return;

$order_id = CSalePaySystemAction::GetParamValue("ORDER_ID");
$sum = CSalePaySystemAction::GetParamValue("SHOULD_PAY");
$payment_status = CSalePaySystemAction::GetParamValue("PAYMENT_STATUS","");
$payment_message = CSalePaySystemAction::GetParamValue("PAYMENT_MESSAGE","");
$arOrder = CSaleOrder::GetByID($order_id);

if  ($payment_status && $payment_status != $arOrder['STATUS_ID']){
	if($payment_message) {
		echo "<div class='vampirus_yandex_payment_message'>".$payment_message."</div>";
	}
} else {
	if (CSalePaySystemAction::GetParamValue("CURRENCY")){
		$sum = number_format(CCurrencyRates::ConvertCurrency($sum,CSalePaySystemAction::GetParamValue("CURRENCY"),'RUB'),2,'.','');
	}
	$app_id = CVampiRUSYandexPayment::GetAppId();
	$wallet = CVampiRUSYandexPayment::GetWallet();
	$domain = (CSalePaySystemAction::GetParamValue("DOMAIN",false))?CSalePaySystemAction::GetParamValue("DOMAIN"):$_SERVER['SERVER_NAME'];
	$domain = str_ireplace(array('http://','https://'),'',rtrim($domain,'/'));
	$path = CVampiRUSYandexPayment::getSchema().$domain.'/bitrix/tools/yandex_result.php';


	$bills = CVampiRUSYandexPayment::getBills($order_id, $sum, 0 /* CSalePaySystemAction::GetParamValue("CARD")*/);
	foreach($bills as $bill) {
		$bill['SUM'] = number_format($bill['SUM'],2,'.','');
		if($bill['PAY']==1){
			echo GetMessage("VAMPIRUS.YANDEX_ORDER_ALREADY_PAY",array('#SUM#'=>$bill['SUM']));
		} else {
			switch(CSalePaySystemAction::GetParamValue("CARD")) {
				case '1': $payment_type = 'AC';break;
				case '2': $payment_type = 'MC';break;
				default: $payment_type = 'PC';
			}
			echo GetMessage("VAMPIRUS.YANDEX_ORDER_PAY_SUM",array('#SUM#'=>$bill['SUM']));
				$rsSites = CSite::GetByID(SITE_ID);
				$arSite = $rsSites->Fetch();
				$domain = (CSalePaySystemAction::GetParamValue("DOMAIN",false))?CSalePaySystemAction::GetParamValue("DOMAIN"):$_SERVER['SERVER_NAME'];
				$domain = str_ireplace(array('http://','https://'),'',rtrim($domain,'/'));
				$order_url = CVampiRUSYandexPayment::getSchema().$domain.'/personal/order/';
				$order_number = isset($arOrder['ACCOUNT_NUMBER'])?$arOrder['ACCOUNT_NUMBER']:$order_id;
			?>
		<form method="POST" name="yandexapi_form" action="https://money.yandex.ru/quickpay/confirm.xml">
		<input type="hidden" name="receiver" value="<?=$wallet?>">
		<input type="hidden" name="formcomment" value="<?=$arSite['SITE_NAME']?>">
		<input type="hidden" name="short-dest" value="<?=GetMessage("VAMPIRUS.YANDEX_ORDER_PAY");?>">
		<input type="hidden" name="label" value="<?=$bill['BILL_ID']?>">
		<input type="hidden" name="quickpay-form" value="shop">
		<input type="hidden" name="targets" value="<?=GetMessage("VAMPIRUS.YANDEX_ORDER_PAYMENT", array('#ORDER_NUMBER#' => $order_number))?>">
		<input type="hidden" name="sum" value="<?=$bill['SUM']?>" >
		<input type="hidden" name="need-fio" value="false">
		<input type="hidden" name="need-email" value="false" >
		<input type="hidden" name="need-phone" value="false">
		<input type="hidden" name="need-address" value="false">
		<input type="hidden" name="paymentType" value="<?php echo $payment_type;?>">
		<input type="hidden" name="successURL" value="<?php echo $order_url; ?>">
		<input type="submit" class="vampirus_yandex_submit_button" value="<?=GetMessage("VAMPIRUS.YANDEX_PAY");?>" />
		<p class="mb-2"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_DESCRIPTION');?></p>
		<p class="mb-2"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_DESCRIPTION_SUM')." ".SaleFormatCurrency($params['SUM'], $params['CURRENCY']);?></p>
		<?if (isset($params['FIELDS'])):?>
			<fieldset class="form-group">
				<?foreach ($params['FIELDS'] as $field):?>
					<?if (in_array($field, $params['PHONE_FIELDS'])):?>
						<label for="<?=$field?>"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_'.ToUpper($params['PAYMENT_METHOD']).'_'.ToUpper($field));?>:</label>
						<input name="<?=$field;?>" type="text" style="max-width: 300px;" id="<?=$field;?>" class="form-control js-paysystem-yandex-input-phone" value="" autocomplete="off" placeholder="">
					<?else:?>
						<label for="<?=$field;?>"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_'.ToUpper($params['PAYMENT_METHOD']).'_'.ToUpper($field));?></label>
						<input name="<?=$field;?>" type="text" style="max-width: 300px;" id="<?=$field;?>" class="form-control" placeholder="<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_'.ToUpper($params['PAYMENT_METHOD']).'_'.ToUpper($field));?>">
					<?endif;?>
				<?endforeach;?>
			</fieldset>
		<?endif;?>
		<input class="btn btn-primary pl-4 pr-4" name="BuyButton" value="<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_BUTTON_NEXT')?>" type="submit">
		</form>
	<? } ?>
	<br>
<? }
} ?>