<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

?>
<?
$bDefaultColumns = $arResult["GRID"]["DEFAULT_COLUMNS"];
$colspan = ($bDefaultColumns) ? count($arResult["GRID"]["HEADERS"]) : count($arResult["GRID"]["HEADERS"]) - 1;
?>
<? if ($arResult["BASKET_ITEMS"]): ?>
    <h3 class="aside__title"><?= Loc::getMessage("BASKET") ?></h3>
    <div class="card mb-4">
        <div class="basket-aside">
            <div>
                <? foreach ($arResult["BASKET_ITEMS"] as $basketItem): ?>
                    <div class="basket-aside__item">
                        <div class="basket-aside__image">
                            <img class="img-fluid" src="<?= $basketItem["PICTURE"] ?>" alt="">
                        </div>
                        <div class="basket-aside__body">
                            <div class="font-weight-bold"><?= $basketItem["SUM"] ?></div>
                            <div><?= $basketItem["NAME"] ?></div>
                            <div><?= Loc::getMessage("QUANTITY") ?><?= $basketItem["QUANTITY"] ?></div>
                            <? if (!empty($basketItem["PROPS"])): ?>
                                <ul class="basket-aside__list">
                                    <? foreach ($basketItem["PROPS"] as $prop): ?>
                                        <li><?= $prop["NAME"] ?>: <?= $prop["VALUE"] ?></li>
                                    <? endforeach; ?>
                                </ul>
                            <? endif; ?>
                        </div>
                    </div>
                <? endforeach; ?>
            </div>
            <div class="basket-aside__footer">
                <div class="d-flex"><?= Loc::getMessage("GOODS") ?><span class="basket-aside__pieces"><?= Loc::getMessage("PCS") ?></span>
                    <div class="ml-auto"><?= $arResult["ORDER_PRICE_FORMATED"] ?></div>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <div><?= Loc::getMessage("TOTAL_WITHOUT_DELIV") ?></div>
                <div class="basket-aside__total"><?= $arResult["ORDER_PRICE_FORMATED"] ?></div>
            </div>
        </div>
    </div>
<? endif ?>