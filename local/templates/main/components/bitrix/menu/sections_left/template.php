<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

?>
<? if (!empty($arResult)): ?>
    <ul class="nav flex-column" style="display: block">
        <? foreach ($arResult as $item): ?>
            <li class="nav-item">
                <a class="nav-link <?= isset($item["PARAMS"]["CHILDREN"]) || !empty($item["PARAMS"]["SUBMENU"]) ? "has-submenu" : "" ?>
                <?= $item["PARAMS"]["CSS_CLASS"] ?>"
                    <?//If it's point of separation, then hide href, because point is empty
                    if (!empty($item["TEXT"])): ?>
                        href="<?= !isset($item["PARAMS"]["CHILDREN"]) ? $item["LINK"] : '' ?>"
                    <? endif; ?>
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
                <? elseif (isset($item["PARAMS"]["CHILDREN"])): ?>
                    <ul class="nav flex-column submenu">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $item["LINK"] ?>">
                                <?= Loc::getMessage('ALL') ?>
                            </a>
                        </li>
                        <? foreach ($item["PARAMS"]["CHILDREN"] as $subItem): ?>
                            <li class="nav-item">
                                <? if ($subItem[3]["CHILDREN"]): ?>
                                    <a class="nav-link has-submenu" href>
                                        <?= $subItem[0] ?>
                                    </a>
                                    <ul class="nav flex-column submenu">
                                        <li class="nav-item">
                                            <a class="nav-link" href="<?= $subItem[1] ?>">
                                                <?= Loc::getMessage('ALL') ?>
                                            </a>
                                        </li>
                                        <? foreach ($subItem[3]["CHILDREN"] as $subSubItem): ?>
                                            <li class="nav-item">
                                                <a class="nav-link" href="<?= $subSubItem[1] ?>">
                                                    <?= $subSubItem[0] ?>
                                                </a>
                                            </li>
                                        <? endforeach; ?>
                                    </ul>
                                <? else: ?>
                                    <a class="nav-link" href="<?= $subItem[1] ?>">
                                        <?= $subItem[0] ?>
                                    </a>
                                <? endif; ?>
                            </li>
                        <? endforeach; ?>
                    </ul>
                <? endif; ?>
            </li>
        <? endforeach; ?>
    </ul>
<? endif; ?>
