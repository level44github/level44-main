<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

?>

<input type="hidden" name="BUYER_STORE" id="BUYER_STORE" value="<?= $arResult["BUYER_STORE"] ?>"/>

<? if (!empty($arResult["DELIVERY"])): ?>
    <fieldset class="fieldset">
        <legend>2. <?= Loc::getMessage("DELIVERY") ?></legend>
        <div class="checkout__radio" id="delivery">
            <? foreach ($arResult["DELIVERY"] as $key => $delivery):
                if ($delivery["CALCULATE_INVALID"]) {
                    continue;
                }
                ?>
                <div class="card option">
                    <a class="option__header
                    <?= $delivery["CHECKED"] ? "" : "collapsed" ?> js-delivery-link"
                       data-toggle="collapse" href="#delivery<?= $key ?>"
                       role="button" aria-expanded="<?= $delivery["CHECKED"] ? "true" : "false" ?>"
                       aria-controls="delivery<?= $key ?>"
                       data-target-label="delivery<?= $key ?>label"
                    >
                        <div class="option__title"><?= $delivery["NAME"] ?></div>
                        <div class="option__hint"><span><?= $delivery["PRICE_PERIOD_TEXT"] ?></span>
                            <? if ($delivery["DOLLAR_PRICE"]): ?>
                                &middot; <span><?= $delivery["DOLLAR_PRICE"] ?></span>
                            <? endif; ?>
                        </div>
                    </a>
                    <label style="display: none;" for="ID_DELIVERY_ID_<?= $delivery["ID"] ?>" id="delivery<?= $key ?>label"></label>
                    <input id="ID_DELIVERY_ID_<?= $delivery["ID"] ?>"
                           name="<?= htmlspecialcharsbx($delivery["FIELD_NAME"]) ?>"
                           class="js-delivery-input"
                           type="radio"
                           style="display: none;"
                           value="<?= $delivery["ID"] ?>"
                        <?= ($delivery["CHECKED"]) ? " checked" : "" ?>
                           onclick="submitForm();"
                    >
                    <div class="collapse <?= $delivery["CHECKED"] ? "show" : "" ?>" id="delivery<?= $key ?>"
                         data-parent="#delivery">
                        <div class="option__body">
                            <? if (in_array($delivery["ID"], CDeliverySDEK::getDeliveryId('pickup'))): ?>
                                <? if (!empty($arResult["ORDER_PROP_ADDRESS_SDEK"]) && $arResult["ORDER_PROP_ADDRESS_SDEK"]["TYPE"] === "STRING"): ?>
                                    <div class="form-group">
                                        <input class="form-control js-form__control"
                                               type="text"
                                               style="display: none"
                                               id="form-delivery<?= $key ?>-address"
                                               maxlength="250"
                                               data-delivery="<?= $delivery["ID"] ?>"
                                               size="<?= $arResult["ORDER_PROP_ADDRESS_SDEK"]["SIZE1"] ?>"
                                               name="<?= $arResult["ORDER_PROP_ADDRESS_SDEK"]["FIELD_NAME"] ?>"
                                               value="<?= $arResult["ORDER_PROP_ADDRESS_SDEK"]["VALUE"] ?>"
                                               data-prop="<?= $arResult["ORDER_PROP_ADDRESS_SDEK"]["CODE"] ?>"
                                        >
                                    </div>
                                <? endif; ?>
                                <div class="form-group">
                                    <div id="cdek-pickup">
                                        <div class="sdek-loading">
                                            <span class="spinner-border text-dark" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <? else: ?>
                                <? if (!empty($arResult["ORDER_PROP_ADDRESS"]) && $arResult["ORDER_PROP_ADDRESS"]["TYPE"] === "STRING"):
                                    $required = $arResult["ORDER_PROP_ADDRESS"]["REQUIRED"];
                                    ?>
                                    <div class="form-group">
                                        <label for="form-delivery<?= $key ?>-address"><?= $arResult["ORDER_PROP_ADDRESS"]["NAME"] ?></label>
                                        <input class="form-control js-form__control js-address-field <?= $required ? "is-required" : "" ?>"
                                               type="text"
                                               id="form-delivery<?= $key ?>-address"
                                               maxlength="250"
                                               data-delivery="<?= $delivery["ID"] ?>"
                                               size="<?= $arResult["ORDER_PROP_ADDRESS"]["SIZE1"] ?>"
                                               name="<?= $arResult["ORDER_PROP_ADDRESS"]["FIELD_NAME"] ?>-fake"
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
                                        </script>
                                    </div>
                                <? endif; ?>
                            <? endif; ?>
                        </div>
                    </div>
                </div>
            <? endforeach; ?>
        </div>
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
