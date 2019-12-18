<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<? if (!empty($arResult["PAY_SYSTEM"])): ?>
    <fieldset class="fieldset">
        <legend>3. Оплата</legend>
        <div class="accordion" id="payment">
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
                        <div class="option__title"><?= $paySystem["NAME"] ?></div>
                    </a>
                    <label for="pay_system<?= $paySystem["ID"] ?>input"
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
                    <div class="collapse <?= $paySystem["CHECKED"] ? "show" : "" ?>"
                         id="payment<?= $paySystem["ID"] ?>"
                         data-parent="#payment"
                    >
                    </div>
                </div>
            <? endforeach; ?>
        </div>
    </fieldset>
<? endif; ?>