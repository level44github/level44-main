<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$langNames = [
    'ru' => 'Русский',
    'en' => 'English',
];
?>

<? if ($arResult["SITES"]): ?>
    <ul class="nav flex-row footer__mobile-langs">
        <? foreach ($arResult["SITES"] as $key => $arSite): ?>
            <li>
                <a class="footer__link <?= $arSite['CURRENT'] === 'Y' ? 'active' : '' ?>"
                   href="<?= $arSite["DIR"] . $arResult["CURRENT_DIR"] ?>"><?= $langNames[$arSite["LANG"]] ?></a>
            </li>
        <? endforeach; ?>
    </ul>
<? endif; ?>