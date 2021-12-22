<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<? if (!empty($arResult)): ?>
    <div class="catalog__nav js-catalog__nav">
        <? foreach ($arResult as $item): ?>
            <div class="catalog__nav-item">
                <a class="catalog__nav-link <?= $item["SELECTED"] ? "active" : "" ?> <?= $item["PARAMS"]["CSS_CLASS"] ?>"
                   href="<?= $item["LINK"] ?>"><?= $item["TEXT"] ?></a>
            </div>
        <? endforeach; ?>
    </div>
<? endif; ?>
