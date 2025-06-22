<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<? if (!empty($arResult)): ?>
    <ul class="nav flex-column profile-mobile__nav d-md-none">
        <? foreach ($arResult as $item): ?>
            <li class="nav-aside__item">
                <a class="nav-aside__link <?= $item['SELECTED'] ? 'active' : '' ?>" href="<?= $item['LINK'] ?>">
                    <?= $item['TEXT'] ?>
                    <? if ($item['PARAMS']['IS_FAVORITES']): ?>
                        <span class="count">148 </span>
                    <? endif; ?>
                </a>
            </li>
        <? endforeach; ?>
    </ul>
<? endif; ?>