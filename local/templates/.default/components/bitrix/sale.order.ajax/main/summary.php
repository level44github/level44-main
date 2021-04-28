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
                            <div class="font-weight-bold"><span><?= $basketItem["SUM"] ?></span>
                                <? if ($basketItem["PRICE_DOLLAR"]): ?>
                                    &middot; <span><?= $basketItem["PRICE_DOLLAR"] ?></span>
                                <? endif; ?>
                            </div>
                            <div class="basket-aside__price-crossed"><span><?= $basketItem["SUM"] ?></span>
                                <? if ($basketItem["PRICE_DOLLAR"]): ?>
                                    &middot; <span><?= $basketItem["PRICE_DOLLAR"] ?></span>
                                <? endif; ?>
                            </div>
                            <div><?= $basketItem["NAME"] ?></div>
                            <div><?= Loc::getMessage("QUANTITY") ?><?= $basketItem["QUANTITY"] ?></div>
                            <? if (!empty($basketItem["PROPS"])): ?>
                                <ul class="basket-aside__list">
                                    <? foreach ($basketItem["PROPS"] as $prop): ?>
                                        <? if ($prop["CODE"] === "COLOR_REF"): ?>
                                            <li><?= Loc::getMessage("PROP_COLOR") ?>: <?= $prop["VALUE"] ?></li>
                                        <? endif; ?>
                                        <? if ($prop["CODE"] === "SIZE_REF"): ?>
                                            <li><?= Loc::getMessage("PROP_SIZE") ?>: <?= $prop["VALUE"] ?></li>
                                        <? endif; ?>
                                    <? endforeach; ?>
                                </ul>
                            <? endif; ?>
                        </div>
                    </div>
                <? endforeach; ?>
            </div>
            <div class="basket-aside__footer">
                <div class="d-flex"><?= Loc::getMessage("GOODS") ?>
                    <span class="basket-aside__pieces">
                        <?= $arResult["BASKET_ITEMS_QUANTITY"] ?>
                        <?= Loc::getMessage("PCS") ?>
                    </span>
                    <div class="ml-auto">
                        <div>
                            <span><?= $arResult["ORDER_PRICE_FORMATED"] ?></span>
                            <? if ($arResult["SUM_PRICE_DOLLAR"]): ?>
                                &middot; <span> <?= $arResult["SUM_PRICE_DOLLAR"] ?></span>
                            <? endif; ?>
                        </div>
                        <div class="basket-aside__price-crossed">
                            <span><?= $arResult["ORDER_PRICE_FORMATED"] ?></span>
                            <? if ($arResult["SUM_PRICE_DOLLAR"]): ?>
                                &middot; <span> <?= $arResult["SUM_PRICE_DOLLAR"] ?></span>
                            <? endif; ?>
                        </div>
                    </div>
                </div>
                <div class="d-flex"><?= Loc::getMessage("DELIVERY") ?>
                    <div class="ml-auto"><span><?= $arResult["CURRENT_DELIVERY"]["PRICE_FORMATED"] ?></span>
                        <? if ($arResult["CURRENT_DELIVERY"]["DOLLAR_PRICE"]): ?>
                            &middot; <span> <?= $arResult["CURRENT_DELIVERY"]["DOLLAR_PRICE"] ?></span>
                        <? endif; ?>
                    </div>
                </div>
            </div>
            <div class="d-flex">
                <div class="basket-aside__total"><span><?= $arResult["ORDER_TOTAL_PRICE"] ?></span>
                    <? if ($arResult["ORDER_TOTAL_PRICE_DOLLAR"]): ?>
                        &middot; <span> <?= $arResult["ORDER_TOTAL_PRICE_DOLLAR"] ?></span>
                    <? endif; ?>
                </div>
            </div>
        </div>
    </div>
<? endif ?>
