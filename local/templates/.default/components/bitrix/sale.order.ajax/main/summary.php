<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<?
$bDefaultColumns = $arResult["GRID"]["DEFAULT_COLUMNS"];
$colspan = ($bDefaultColumns) ? count($arResult["GRID"]["HEADERS"]) : count($arResult["GRID"]["HEADERS"]) - 1;
?>
<? if ($arResult["BASKET_ITEMS"]): ?>
    <h3 class="aside__title">Состав заказа</h3>
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
                            <div>Количество: <?= $basketItem["QUANTITY"] ?></div>
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
                <div class="d-flex">
                    Товары
                    <span class="basket-aside__pieces"><?= $arResult["BASKET_ITEMS_QUANTITY"] ?> шт</span>
                    <div class="ml-auto"><?= $arResult["ORDER_PRICE_FORMATED"] ?></div>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <div>Итого без доставки</div>
                <div class="basket-aside__total"><?= $arResult["ORDER_PRICE_FORMATED"] ?></div>
            </div>
        </div>
    </div>
<? endif ?>
