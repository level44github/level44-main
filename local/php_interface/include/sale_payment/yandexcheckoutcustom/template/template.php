<?php
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$sum = round($params['SUM'], 2);
?>

<div class="mb-4 ya_cashier" >
	<div class="d-flex align-items-center mb-3">
		<div class="col-auto pl-0">
			<a class="btn btn-primary js-ya_cashier_link" href="<?=$params['URL'];?>"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_BUTTON_PAID')?></a>
		</div>
	</div>
</div>