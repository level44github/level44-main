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
    <div class="card mb-4 js-summary-block" data-site-id="<?= SITE_ID ?>">
        <div class="basket-aside">
            <div>
                <? foreach ($arResult["BASKET_ITEMS"] as $basketItem): ?>
                    <div class="basket-aside__item js-summary-item"
                         data-product-id="<?= $basketItem["PRODUCT_ID"] ?>"
                         data-basket-item-id="<?= $basketItem["ID"] ?>"
                    >
                        <div class="basket-aside__main-block">
                            <a class="basket-aside__remove js-remove-unavailable" style="display: none;" href="#">
                                <svg class="icon icon-close ">
                                    <use xlink:href="#close"></use>
                                </svg>
                            </a>
                            <div class="basket-aside__image">
                                <img class="img-fluid" src="<?= $basketItem["PICTURE"] ?>" alt="">
                            </div>
                            <div class="basket-aside__body">
                                <? if (!empty($basketItem["SHOW_THREE_PRICES"])): ?>
                                    <!-- Отображение 3 цен для товаров категории sale -->
                                    <? 
                                    // Определяем значения для отображения
                                    // 1. Оригинальная цена (без скидки)
                                    $originalPriceFormated = $basketItem["SUM_ORIGINAL_PRICE_FORMATED"] ?? $basketItem["SUM_BASE_FORMATED"] ?? '';
                                    
                                    // 2. Цена после первой скидки (промежуточная, до доп скидки)
                                    // Используем SUM_PRICE_BEFORE_ADDITIONAL_DISCOUNT_FORMATED (рассчитанная в applyDiscounts)
                                    // или SUM_BASE (цена после первой скидки из корзины)
                                    $intermediatePriceSum = null;
                                    if (!empty($basketItem["SUM_PRICE_BEFORE_ADDITIONAL_DISCOUNT_FORMATED"])) {
                                        $intermediatePriceSum = $basketItem["SUM_PRICE_BEFORE_ADDITIONAL_DISCOUNT_FORMATED"];
                                    } elseif (isset($basketItem["SUM_BASE"]) && $basketItem["SUM_BASE"] > 0) {
                                        // Используем SUM_BASE (цена после первой скидки)
                                        $intermediatePriceSum = CCurrencyLang::CurrencyFormat($basketItem["SUM_BASE"], "RUB");
                                    } elseif (isset($basketItem["PRICE_BEFORE_ADDITIONAL_DISCOUNT"]) && $basketItem["PRICE_BEFORE_ADDITIONAL_DISCOUNT"] > 0) {
                                        // Рассчитываем из PRICE_BEFORE_ADDITIONAL_DISCOUNT
                                        $qty = $basketItem["QUANTITY"] ?? 1;
                                        $intermediatePriceSum = CCurrencyLang::CurrencyFormat($basketItem["PRICE_BEFORE_ADDITIONAL_DISCOUNT"] * $qty, "RUB");
                                    }
                                    
                                    // 3. Основная цена (после дополнительной скидки) - используем SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT_FORMATED
                                    // если есть дополнительная скидка, иначе используем цену после первой скидки
                                    $mainPriceFormated = null;
                                    if (!empty($basketItem["ADDITIONAL_DISCOUNT_PERCENT"]) && !empty($basketItem["SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT_FORMATED"])) {
                                        // Если есть дополнительная скидка, показываем цену после неё
                                        $mainPriceFormated = $basketItem["SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT_FORMATED"];
                                    } elseif (!empty($basketItem["SUM_PRICE_BEFORE_ADDITIONAL_DISCOUNT_FORMATED"])) {
                                        // Если нет дополнительной скидки, показываем цену после первой скидки
                                        $mainPriceFormated = $basketItem["SUM_PRICE_BEFORE_ADDITIONAL_DISCOUNT_FORMATED"];
                                    } elseif (isset($basketItem["SUM_BASE"]) && $basketItem["SUM_BASE"] > 0) {
                                        // Используем SUM_BASE (цена после первой скидки)
                                        $mainPriceFormated = CCurrencyLang::CurrencyFormat($basketItem["SUM_BASE"], "RUB");
                                    } elseif (isset($basketItem["SUM"])) {
                                        $mainPriceFormated = $basketItem["SUM"];
                                    }
                                    ?>
                                    <!-- 1. Зачеркнутая цена (без скидки) -->
                                    <? if (!empty($originalPriceFormated)): ?>
                                        <div class="basket-aside__price-crossed">
                                            <span style="text-decoration: line-through; color: #999;"><?= $originalPriceFormated ?></span>
                                        </div>
                                    <? endif; ?>
                                    <!-- 2. Цена после первой скидки (зачеркнутая, промежуточная) - показываем только если есть дополнительная скидка -->
                                    <? if (!empty($basketItem["ADDITIONAL_DISCOUNT_PERCENT"]) && $basketItem["ADDITIONAL_DISCOUNT_PERCENT"] > 0 && !empty($intermediatePriceSum)): ?>
                                        <div class="basket-aside__price-intermediate">
                                            <span style="text-decoration: line-through; color: #999;"><?= $intermediatePriceSum ?></span>
                                        </div>
                                    <? endif; ?>
                                    <!-- 3. Основная цена в чекауте - всегда цена после первой скидки, дополнительная скидка применяется только при сохранении заказа -->
                                    <? if (!empty($mainPriceFormated)): ?>
                                        <div class="font-weight-bold product__final-price">
                                            <span><?= $mainPriceFormated ?></span>
                                            <? if ($basketItem["PRICE_DOLLAR"]): ?>
                                                &middot; <span><?= $basketItem["PRICE_DOLLAR"] ?></span>
                                            <? endif; ?>
                                        </div>
                                    <? endif; ?>
                                <? else: ?>
                                    <!-- Стандартное отображение для обычных товаров -->
                                    <div class="font-weight-bold <?= $basketItem["oldPrice"] ? "product__final-price" : "" ?>">
                                        <span><?= $basketItem["SUM"] ?></span>
                                        <? if ($basketItem["PRICE_DOLLAR"]): ?>
                                            &middot; <span><?= $basketItem["PRICE_DOLLAR"] ?></span>
                                        <? endif; ?>
                                    </div>
                                    <? if (!empty($basketItem["oldPrice"])): ?>
                                        <div class="basket-aside__price-crossed"><span><?= $basketItem["oldPriceFormat"] ?></span>
                                            <? if ($basketItem["PRICE_DOLLAR"]): ?>
                                                &middot; <span><?= $basketItem["oldPriceDollarFormat"] ?></span>
                                            <? endif; ?>
                                        </div>
                                    <? endif; ?>
                                <? endif; ?>
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
                        <div class="basket-aside--error-container js-unavailable-error" style="display: none">
                            <div class="basket-aside--error"><?= Loc::getMessage("NOT_AVAILABLE_PRODUCT") ?></div>
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
                        <div class="<?= $arResult["SHOW_OLD_SUM_PRICE"] ? "" : "" ?>">
                            <? if ($arResult["SHOW_OLD_SUM_PRICE"]){ ?>
                                <span><?= $arResult['JS_DATA']['TOTAL']["OLD_SUM_PRICE"] ?></span>
                            <? }else{?>
                                <span><?= $arResult['JS_DATA']['TOTAL']["PRICE_WITHOUT_DISCOUNT"] ?></span>
                            <?} ?>

                            <? if ($arResult["SUM_PRICE_DOLLAR"]): ?>
                                &middot; <span> <?= $arResult["SUM_PRICE_DOLLAR"] ?></span>
                            <? endif; ?>
                        </div>
                        <? if ($arResult["SHOW_OLD_SUM_PRICE"]): ?>
                            <div class="">
                                <span><?= $arResult["OLD_SUM_PRICE"] ?></span>
                                <? if ($arResult["SUM_PRICE_DOLLAR"]): ?>
                                    &middot; <span> <?= $arResult["OLD_SUM_PRICE_DOLLAR"] ?></span>
                                <? endif; ?>
                            </div>
                        <? endif; ?>
                    </div>
                </div>



                <?if ($arResult["SHOW_OLD_SUM_PRICE"]!=null){?>
                    <div class="d-flex">Скидка
                        <div class="ml-auto product__final-price">
                            <? if (isset($arResult["TOTAL_DISCOUNT_AMOUNT"]) && $arResult["TOTAL_DISCOUNT_AMOUNT"] > 0): ?>
                                - <?= $arResult["TOTAL_DISCOUNT_AMOUNT_FORMATED"] ?>
                            <? else: ?>
                                - <? echo CCurrencyLang::CurrencyFormat($arResult["OLD_SUM_PRICE_VALUE"]-$arResult[ 'JS_DATA']['TOTAL']['PRICE_WITHOUT_DISCOUNT_VALUE'], "RUB") ;?>
                            <? endif; ?>
                        </div>
                    </div>
                <?}?>

                <?if ($arResult['JS_DATA']['TOTAL']['DISCOUNT_PRICE']!=0){?>
                    <? 
                    // Проверяем, есть ли товары с дополнительной скидкой категории sale
                    // Если есть, не показываем поле "Дополнительная скидка", так как скидка уже учтена в ценах
                    $hasSaleItemsWithAdditionalDiscount = false;
                    foreach ($arResult["BASKET_ITEMS"] as $item) {
                        if (!empty($item['SHOW_THREE_PRICES']) && !empty($item['ADDITIONAL_DISCOUNT_PERCENT'])) {
                            $hasSaleItemsWithAdditionalDiscount = true;
                            break;
                        }
                    }
                    ?>
                    <?if (!$hasSaleItemsWithAdditionalDiscount):?>
                        <div class="d-flex"><?= Loc::getMessage("ADDDISCOUNT") ?>Дополнительная скидка


                            <div class="ml-auto product__final-price">
                                -<?=$arResult['JS_DATA']['TOTAL']['BASKET_PRICE_DISCOUNT_DIFF'];?>

                            </div>
                        </div>
                    <?endif;?>
                <?}?>






                <div class="d-flex"><?= Loc::getMessage("DELIVERY") ?>

                    <div class="ml-auto">

                        <?if ($arResult["CURRENT_DELIVERY"]["DELIVERY_DISCOUNT_PRICE_FORMATED"]!=null){?>
                            <div class="product__final-price k-delivery-discount">
                                <span><?=$arResult["CURRENT_DELIVERY"]["DELIVERY_DISCOUNT_PRICE_FORMATED"]?></span>
                            </div>

                        <?}else{?>
                            <span><?= $arResult["CURRENT_DELIVERY"]["PRICE_FORMATED"] ?></span>
                        <?}?>
                        <? if ($arResult["CURRENT_DELIVERY"]["DOLLAR_PRICE"]): ?>
                            &middot; <span> <?= $arResult["CURRENT_DELIVERY"]["DOLLAR_PRICE"] ?></span>
                        <? endif; ?>
                    </div>
                </div>

                <?if ((int)$arResult['BONUSPAY']['USER_VALUE']!=0){?>
                    <div class="d-flex">Бонусами


                        <div class="ml-auto product__final-price">
                            -<?=$arResult['BONUSPAY']['USER_VALUE'];?> руб.

                        </div>
                    </div>
                <?}?>


            </div>
            <div class="d-flex">
                <div class="basket-aside__total"><span><?= $arResult["ORDER_TOTAL_PRICE_NEW"] ?></span>
                    <? if ($arResult["ORDER_TOTAL_PRICE_DOLLAR"]): ?>
                        &middot; <span> <?= $arResult["ORDER_TOTAL_PRICE_DOLLAR"] ?></span>
                    <? endif; ?>
                </div>
            </div>
        </div>
    </div>
<? endif ?>
