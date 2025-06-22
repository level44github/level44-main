<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<? if (!empty($arResult)): ?>
    <div class="col-md-2 d-none d-md-block" id="profile-menu">
        <div class="nav-aside">
            <ul class="nav flex-column">
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
        </div>
    </div>
<? endif; ?>