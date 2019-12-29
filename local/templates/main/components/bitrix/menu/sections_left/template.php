<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
use Bitrix\Main\Localization\Loc;
?>
<? if (!empty($arResult)): ?>
    <h4 class="px-3"><?= Loc::getMessage("CATALOG") ?></h4>
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