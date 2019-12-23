<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<? if (!empty($arResult)): ?>
    <h4 class="px-3">Каталог</h4>
    <ul class="nav flex-column">
        <? foreach ($arResult as $item): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= $item["LINK"] ?>">
                    <?= $item["TEXT"] ?>
                </a>
            </li>
        <? endforeach; ?>
    </ul>
<? endif; ?>