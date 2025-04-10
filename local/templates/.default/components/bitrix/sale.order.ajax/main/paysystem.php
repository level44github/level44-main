<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
use Bitrix\Main\Localization\Loc;
?>
<? if (!empty($arResult["PAY_SYSTEM"])): ?>
    <fieldset class="fieldset">
        <legend><?= Loc::getMessage("PAYMENT") ?></legend>
        <div class="checkout__radio" id="payment">
            <? foreach ($arResult["PAY_SYSTEM"] as $paySystem): ?>
                <div class="card option">
                    <a class="option__header
                    <?= $paySystem["CHECKED"] ? "" : "collapsed" ?> js-pay_system-link"
                       data-toggle="collapse"
                       href="#payment<?= $paySystem["ID"] ?>"
                       role="button"
                       aria-expanded="<?= $paySystem["CHECKED"] ? "true" : "false" ?>"
                       aria-controls="payment<?= $paySystem["ID"] ?>"
                       data-target-label="pay_system<?= $paySystem["ID"] ?>label"
                    >
                        <div class="option__title"><?= Loc::getMessage($paySystem["CODE"] . '_PAY_SYSTEM_NAME') ?></div>
                        <? if (!empty($paySystem["ICONS"])): ?>
                            <? foreach ($paySystem["ICONS"] as $icon): ?>
                                <div class="option__system <?= $icon ?>"></div>
                            <? endforeach; ?>
                        <? endif; ?>
                    </a>
                    <label style="display: none;" for="pay_system<?= $paySystem["ID"] ?>input"
                           id="pay_system<?= $paySystem["ID"] ?>label"></label>
                    <input id="pay_system<?= $paySystem["ID"] ?>input"
                           name="PAY_SYSTEM_ID"
                           class="js-pay_system-input"
                           type="radio"
                           style="display: none;"
                           value="<?= $paySystem["ID"] ?>"
                        <?= ($paySystem["CHECKED"]) ? " checked" : "" ?>
                           onclick="submitForm();"
                    >
                </div>
            <? endforeach; ?>
        </div>
    </fieldset>
<? endif; ?>
