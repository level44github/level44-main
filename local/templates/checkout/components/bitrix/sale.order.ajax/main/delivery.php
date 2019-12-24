<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<input type="hidden" name="BUYER_STORE" id="BUYER_STORE" value="<?= $arResult["BUYER_STORE"] ?>"/>

<? if (!empty($arResult["DELIVERY"])): ?>
    <fieldset class="fieldset">
        <legend>2. Доставка</legend>
        <div class="accordion" id="delivery">
            <? foreach ($arResult["DELIVERY"] as $key => $delivery): ?>
                <div class="card option">
                    <a class="option__header
                    <?= $delivery["CHECKED"] ? "" : "collapsed" ?> js-delivery-link"
                       data-toggle="collapse" href="#delivery<?= $key ?>"
                       role="button" aria-expanded="<?= $delivery["CHECKED"] ? "true" : "false" ?>"
                       aria-controls="delivery<?= $key ?>"
                       data-target-label="delivery<?= $key ?>label"
                    >
                        <div class="option__title"><?= $delivery["NAME"] ?></div>
                        <div class="option__hint"><?= $delivery["PRICE_PERIOD_TEXT"] ?></div>
                    </a>
                    <label for="delivery<?= $key ?>input" id="delivery<?= $key ?>label"></label>
                    <input id="delivery<?= $key ?>input"
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
                            <? if ($arResult["ORDER_PROP_ADDRESS"]["TYPE"] === "TEXT"): ?>
                                <div class="form-group">
                                    <label for="form-delivery<?= $key ?>-address"><?= $arResult["ORDER_PROP_ADDRESS"]["NAME"] ?></label>

                                    <input class="form-control js-form__control"
                                           type="text"
                                           id="form-delivery<?= $key ?>-address"
                                           maxlength="250"
                                           size="<?= $arResult["ORDER_PROP_ADDRESS"]["SIZE1"] ?>"
                                           name="<?= $arResult["ORDER_PROP_ADDRESS"]["FIELD_NAME"] ?>"
                                           value="<?= $arResult["ORDER_PROP_ADDRESS"]["VALUE"] ?>"
                                           data-prop="<?= $arResult["ORDER_PROP_ADDRESS"]["CODE"] ?>"
                                           placeholder="<?= $arResult["ORDER_PROP_ADDRESS"]["DESCRIPTION"] ?>"
                                    >

                                </div>
                            <? endif; ?>
                        </div>
                    </div>
                </div>
            <? endforeach; ?>
        </div>
        <div class="form-group">
            <label for="form-comment">Комментарий</label>
            <textarea class="form-control"
                      id="form-comment"
                      placeholder="Особые требования к упаковке или доставке..."
                      name="ORDER_DESCRIPTION"
                      rows="3"
            ><?= $arResult["USER_VALS"]["ORDER_DESCRIPTION"] ?></textarea>
        </div>
    </fieldset>
<? endif; ?>
