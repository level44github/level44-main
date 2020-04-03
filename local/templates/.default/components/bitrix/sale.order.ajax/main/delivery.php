<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
use Bitrix\Main\Localization\Loc;

?>

<input type="hidden" name="BUYER_STORE" id="BUYER_STORE" value="<?= $arResult["BUYER_STORE"] ?>"/>

<? if (!empty($arResult["DELIVERY"])): ?>
    <fieldset class="fieldset">
        <legend>2. <?= Loc::getMessage("DELIVERY") ?></legend>
        <div class="checkout__radio" id="delivery">
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
                        <div class="option__hint"><span><?= $delivery["PRICE_PERIOD_TEXT"] ?></span>
                            <? if ($delivery["DOLLAR_PRICE"]): ?>
                                &middot; <span><?= $delivery["DOLLAR_PRICE"] ?></span>
                            <? endif; ?>
                        </div>
                    </a>
                    <label style="display: none;" for="delivery<?= $key ?>input" id="delivery<?= $key ?>label"></label>
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
                            <? if (!empty($arResult["ORDER_PROP_ADDRESS"]) && $arResult["ORDER_PROP_ADDRESS"]["TYPE"] === "STRING"):
                                $required = $arResult["ORDER_PROP_ADDRESS"]["REQUIRED"];
                                ?>
                                    <div class="form-group">
                                    <label for="form-delivery<?= $key ?>-address"><?= $arResult["ORDER_PROP_ADDRESS"]["NAME"] ?></label>
                                        <input class="form-control js-form__control <?= $required ? "is-required" : "" ?>"
                                               type="text"
                                               id="form-delivery<?= $key ?>-address"
                                               maxlength="250"
                                               data-delivery="<?=$delivery["ID"]?>"
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
                                    </div>
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
