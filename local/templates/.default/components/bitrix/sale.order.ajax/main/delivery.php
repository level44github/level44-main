<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

?>


<?php
if (!function_exists("PrintDelivery")) {
    function PrintDelivery(array $delivery, array $data = [])
    {

        ?>
        <div class="card option">
            <a class="option__header
                    <?= $delivery["CHECKED"] ? "" : "collapsed" ?> js-delivery-link"
               href
               onclick="return false;"
               role="button" aria-expanded="<?= $delivery["CHECKED"] ? "true" : "false" ?>"
               aria-controls="delivery<?= $delivery["ID"] ?>"
               data-target-label="delivery<?= $delivery["ID"] ?>label"
            >
                <div class="option__title"><?= $data["NAME"] ?></div>
                <div class="option__hint"><span><?= $delivery["PRICE_PERIOD_TEXT"] ?></span>
                    <? if ($delivery["DOLLAR_PRICE"]): ?>
                        &middot; <span><?= $delivery["DOLLAR_PRICE"] ?></span>
                    <? endif; ?>
                </div>
            </a>
            <label style="display: none;" for="ID_DELIVERY_ID_<?= $delivery["ID"] ?>"
                   id="delivery<?= $delivery["ID"] ?>label"></label>
            <input id="ID_DELIVERY_ID_<?= $delivery["ID"] ?>"
                   name="<?= htmlspecialcharsbx($delivery["FIELD_NAME"]) ?>"
                   class="js-delivery-input"
                   type="radio"
                   style="display: none;"
                   value="<?= $delivery["ID"] ?>"
                <?= ($delivery["CHECKED"]) ? " checked" : "" ?>
                   onclick="submitForm();"
            >
            <div id="delivery<?= $delivery["ID"] ?>"
                 data-parent="#delivery">
                <div class="option__description">
                    <? if (in_array($delivery["ID"], CDeliverySDEK::getDeliveryId('pickup'))): ?>
                        <? if (!empty($arResult["ORDER_PROP_ADDRESS_SDEK"]) && $arResult["ORDER_PROP_ADDRESS_SDEK"]["TYPE"] === "STRING"): ?>
                            <div class="form-group">
                                <input class="form-control js-form__control"
                                       type="text"
                                       style="display: none"
                                       id="form-delivery<?= $delivery["ID"] ?>-address"
                                       maxlength="250"
                                       data-delivery="<?= $delivery["ID"] ?>"
                                       size="<?= $arResult["ORDER_PROP_ADDRESS_SDEK"]["SIZE1"] ?>"
                                       name="<?= $arResult["ORDER_PROP_ADDRESS_SDEK"]["FIELD_NAME"] ?>"
                                       value="<?= $arResult["ORDER_PROP_ADDRESS_SDEK"]["VALUE"] ?>"
                                       data-prop="<?= $arResult["ORDER_PROP_ADDRESS_SDEK"]["CODE"] ?>"
                                >
                            </div>
                        <? endif; ?>
                        <div class="form-group" <?= !$delivery["CHECKED"] ? 'hidden' : '' ?>>
                            <div id="cdek-pickup">
                                <div class="sdek-loading">
                                            <span class="spinner-border text-dark" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </span>
                                </div>
                            </div>
                        </div>
                    <? endif; ?>

                    <span><?= $data["DESCRIPTION"] ?></span>
                </div>
            </div>
        </div>
        <?
    }
}
?>

    <input type="hidden" name="BUYER_STORE" id="BUYER_STORE" value="<?= $arResult["BUYER_STORE"] ?>"/>

<? if (!empty($arResult["DELIVERY"])):
    $deliveryTypes = $arResult["DELIVERY"];
    ?>
    <fieldset class="fieldset">
        <legend>2. <?= Loc::getMessage("DELIVERY") ?></legend>
        <div class="checkout__radio" id="delivery">
            <?
            if (!empty($deliveryTypes["PICKUP"])) {
                $data = [
                    "NAME"        => Loc::getMessage("DELIVERY_PICKUP_NAME"),
                    "DESCRIPTION" => Loc::getMessage("DELIVERY_PICKUP_DESCRIPTION"),
                ];

                PrintDelivery($deliveryTypes["PICKUP"], $data);
            }

            if (!empty($deliveryTypes["COURIER"])) {
                $data = [
                    "NAME"        => Loc::getMessage("DELIVERY_COURIER_NAME"),
                    "DESCRIPTION" => Loc::getMessage("DELIVERY_COURIER_DESCRIPTION"),
                ];

                PrintDelivery($deliveryTypes["COURIER"], $data);
            }

            if (!empty($deliveryTypes["COURIER_FITTING"])) {
                $data = [
                    "NAME"        => Loc::getMessage("DELIVERY_COURIER_FITTING_NAME"),
                    "DESCRIPTION" => Loc::getMessage("DELIVERY_COURIER_FITTING_DESCRIPTION"),
                ];

                PrintDelivery($deliveryTypes["COURIER_FITTING"], $data);
            }

            if (!empty($deliveryTypes["SHOP"])) {
                $data = [
                    "NAME"        => Loc::getMessage("DELIVERY_SHOP_NAME"),
                    "DESCRIPTION" => Loc::getMessage("DELIVERY_SHOP_DESCRIPTION"),
                ];

                PrintDelivery($deliveryTypes["SHOP"], $data);
            }
            ?>
        </div>

        <? if ($arResult["CURRENT_DELIVERY"]["IS_COURIER"]): ?>
            <div class="form-group">
                <label for="<?= $arResult["ORDER_PROP_ADDRESS"]["FIELD_NAME"] ?>"><?= $arResult["ORDER_PROP_ADDRESS"]["NAME"] ?></label>
                <input class="form-control js-form__control js-address-field <?= $arResult["ORDER_PROP_ADDRESS"]["REQUIRED"] ? "is-required" : "" ?>"
                       type="text"
                       id=" <?= $arResult["ORDER_PROP_ADDRESS"]["FIELD_NAME"] ?>"
                       maxlength="250"
                       size="<?= $arResult["ORDER_PROP_ADDRESS"]["SIZE1"] ?>"
                       name="<?= $arResult["ORDER_PROP_ADDRESS"]["FIELD_NAME"] ?>"
                       value="<?= $arResult["ORDER_PROP_ADDRESS"]["VALUE"] ?>"
                       data-prop="<?= $arResult["ORDER_PROP_ADDRESS"]["CODE"] ?>"
                       placeholder="<?= $arResult["ORDER_PROP_ADDRESS"]["DESCRIPTION"] ?>"
                >
                <div class="invalid-feedback">
                    <?= Loc::getMessage($arResult["ORDER_PROP_ADDRESS"]["ERROR_MES_TYPE"], [
                        "#FIELD#" => $arResult["ORDER_PROP_ADDRESS"]["NAME"]
                    ]) ?>
                </div>

                <script>
                    BX.saleOrderAjax.address.previousValue = <?=Json::encode($arResult["ORDER_PROP_ADDRESS"]["VALUE"])?>;
                    BX.saleOrderAjax.address.errors = {
                        EMPTY: <?=Json::encode(Loc::getMessage($arResult["ORDER_PROP_ADDRESS"]["ERROR_MES_TYPE"], [
                            "#FIELD#" => $arResult["ORDER_PROP_ADDRESS"]["NAME"]
                        ]))?>,
                        NOT_SELECTED: <?=Json::encode(Loc::getMessage('ADDRESS_NOT_SELECTED_ERROR_MES'))?>,
                        MISSED_HOUSE: <?=Json::encode(Loc::getMessage('ADDRESS_MISSED_HOUSE_ERROR_MES'))?>,
                        MISSED_FLAT: <?=Json::encode(Loc::getMessage('ADDRESS_MISSED_FLAT_ERROR_MES'))?>,
                    }
                    BX.saleOrderAjax.address.fieldName = '<?=(string)$arResult["ORDER_PROP_ADDRESS"]["FIELD_NAME"]?>';
                    BX.saleOrderAjax.address.lastAddressOutRussia = <?=Json::encode($arResult["OUT_RUSSIA"])?>;
                </script>
            </div>
        <? endif; ?>

        <div class="form-group">
            <label for="form-comment"><?= Loc::getMessage("COMMENT") ?></label>
            <textarea class="form-control"
                      id="form-comment"
                      placeholder="<?= Loc::getMessage("COMMENT_MESS") ?>"
                      name="ORDER_DESCRIPTION"
                      rows="3"
            ><?= $arResult["USER_VALS"]["ORDER_DESCRIPTION"] ?></textarea>
        </div>
    </fieldset>
<? endif; ?>