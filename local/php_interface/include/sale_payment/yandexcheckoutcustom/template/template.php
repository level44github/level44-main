<?php
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$sum = round($params['SUM'], 2);
?>

<div class="mb-4 ya_cashier" >
	<div class="d-flex align-items-center mb-3">
		<div class="col-auto pl-0">
			<a class="btn btn-dark thank-order__btn btn__fix-width btn-primary js-ya_cashier_link" href="<?=$params['URL'];?>"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_BUTTON_PAID')." ".SaleFormatCurrency($sum, $params['CURRENCY']);?></a>
		</div>
	</div>
</div>