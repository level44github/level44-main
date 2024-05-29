<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
use Bitrix\Main\Localization\Loc;
?>
<? if (!empty($arResult)): ?>
    <ul class="nav flex-column">
        <? foreach ($arResult as $item): ?>
            <li class="nav-item">
                <a class="nav-link <?= !empty($item["PARAMS"]["SUBMENU"]) ? "has-submenu" : "" ?>
                <?= $item["PARAMS"]["CSS_CLASS"] ?>" href="<?= $item["LINK"] ?>"
                >
                    <?= $item["TEXT"] ?>
                </a>
                <? if (!empty($item["PARAMS"]["SUBMENU"])): ?>
                    <ul class="nav flex-column submenu">
                        <? foreach ($item["PARAMS"]["SUBMENU"] as $subItem): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $subItem["PARAMS"]["CSS_CLASS"] ?>"
                                   href="<?= $subItem["LINK"] ?>">
                                    <?= $subItem["TEXT"] ?>
                                </a>
                            </li>
                        <? endforeach; ?>
                    </ul>
                <? endif; ?>
            </li>
        <? endforeach; ?>
    </ul>
<? endif; ?>
