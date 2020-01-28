<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<? if (!empty($arResult)): ?>
    <ul class="nav flex-column">
        <? foreach ($arResult as $item): ?>
            <li class="nav-item">
                <a class="footer__link <?= $item["SELECTED"] ? "active" : "" ?>"
                    <? if ($arResult["EXT_LINKS"]): ?>
                        target="_blank"
                    <? endif; ?>
                   href="<?= $item["LINK"] ?>">
                    <?= $item["TEXT"] ?>
                </a>
            </li>
        <? endforeach; ?>
    </ul>
<? endif; ?>
