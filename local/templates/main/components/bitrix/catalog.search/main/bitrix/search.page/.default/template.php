<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use Bitrix\Main\Localization\Loc;

?>
<h1 class="h3 text-center mb-4"><?= Loc::getMessage("SEARCH") ?>: <?= $arResult["REQUEST"]["QUERY"] ?></h1>