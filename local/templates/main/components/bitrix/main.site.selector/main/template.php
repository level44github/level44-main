<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
} ?>

<? if ($arResult["SITES"]): ?>
    <li class="menu__lang">
        <? foreach ($arResult["SITES"] as $key => $arSite): ?>
            <button class="btn btn-link menu__link"
                    onclick="window.location.href='<?= $arSite["DIR"] . $arResult["CURRENT_DIR"] ?>'"
                    type="button">
                <?= strtoupper($arSite["LANG"]) ?>
            </button>
        <? endforeach; ?>
    </li>
<? endif; ?>