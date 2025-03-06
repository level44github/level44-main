<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

function getContainerClass($params): string
{
    return !empty($params['EXPANDED']) && $params['EXPANDED'] === true ? 'active' : '';
}

function getLinkClass($params): string
{
    return !empty($params['SELECTED']) && $params['SELECTED'] === true ? 'selected' : '';
}

function getSaleClass($params): string
{
    return !empty($params['IS_SALE']) && $params['IS_SALE'] === true ? 'featured' : '';
}

?>
<? if (!empty($arResult)): ?>
    <ul class="nav-menu">
        <? foreach ($arResult as $item): ?>
            <? if (!empty($item["PARAMS"]["CHILDREN"])): ?>
                <li class="nav-menu__item">
                    <div class="nav-menu__link-container <?= getContainerClass($item["PARAMS"]) ?>" role="button"
                         tabindex="0">
                        <span class="nav-menu__link first-level <?= getSaleClass($item["PARAMS"]) ?>">
                            <?= $item["TEXT"] ?>
                        </span>
                        <svg class="icon icon-arrow-down nav-menu__icon">
                            <use xlink:href="#arrow-down"></use>
                        </svg>
                    </div>
                    <ul class="nav-menu__submenu-list second-level <?= getContainerClass($item["PARAMS"]) ?>">
                        <? if (!$item['PARAMS']['IS_TO_CUSTOMERS']): ?>
                            <li class="nav-menu__submenu-item">
                                <div class="nav-menu__link-container">
                                    <a class="nav-menu__link second-level <?= getLinkClass($item["PARAMS"]) ?>"
                                       href="<?= $item["LINK"] ?>">
                                        <?= Loc::getMessage('ALL') ?>
                                    </a>
                                </div>
                            </li>
                        <? endif; ?>
                        <? foreach ($item["PARAMS"]["CHILDREN"] as $child): ?>
                            <? if (!empty($child[3]['CHILDREN'])): ?>
                                <li class="nav-menu__submenu-item">
                                    <div class="nav-menu__link-container <?= getContainerClass($child[3]) ?>"
                                         role="button" tabindex="0">
                                        <span class="nav-menu__link second-level"><?= $child[0] ?></span>
                                        <svg class="icon icon-arrow-down nav-menu__icon">
                                            <use xlink:href="#arrow-down"></use>
                                        </svg>
                                    </div>
                                    <ul class="nav-menu__submenu-list <?= getContainerClass($child[3]) ?>">
                                        <li class="nav-menu__submenu-item">
                                            <div class="nav-menu__link-container">
                                                <a class="nav-menu__link third-level  <?= getLinkClass($child[3]) ?>"
                                                   href="<?= $child[1] ?>">
                                                    <?= Loc::getMessage('ALL') ?>
                                                </a>
                                            </div>
                                        </li>
                                        <? foreach ($child[3]['CHILDREN'] as $subChild): ?>
                                            <li class="nav-menu__submenu-item">
                                                <div class="nav-menu__link-container">
                                                    <a class="nav-menu__link third-level <?= getLinkClass($subChild[3]) ?>"
                                                       href="<?= $subChild[1] ?>">
                                                        <?= $subChild[0] ?>
                                                    </a>
                                                </div>
                                            </li>
                                        <? endforeach; ?>
                                    </ul>
                                </li>
                            <? else: ?>
                                <li class="nav-menu__submenu-item">
                                    <div class="nav-menu__link-container">
                                        <a class="nav-menu__link second-level <?= getLinkClass($child[3]) ?>"
                                           href="<?= $child[1] ?>">
                                            <?= $child[0] ?>
                                        </a>
                                    </div>
                                </li>
                            <? endif; ?>
                        <? endforeach; ?>
                    </ul>
                </li>
            <? else: ?>
                <li class="nav-menu__item">
                    <div class="nav-menu__link-container">
                        <a class="nav-menu__link first-level <?= getLinkClass($item["PARAMS"]) ?>"
                           href="<?= $item["LINK"] ?>">
                            <?= $item["TEXT"] ?>
                        </a>
                    </div>
                </li>
            <? endif; ?>
        <? endforeach; ?>
    </ul>
<? endif; ?>