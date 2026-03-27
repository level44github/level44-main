<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);

if (empty($arResult['ITEMS'])) {
    return;
}
?>
<div class="campaign-look__grid">
    <?php foreach ($arResult['ITEMS'] as $item): ?>
        <?php
        $detailUrl = (string)($item['DETAIL_PAGE_URL'] ?? '');
        $imgSrc = '';
        if (!empty($item['PREVIEW_IMAGES'][0])) {
            $imgSrc = (string)$item['PREVIEW_IMAGES'][0];
        } elseif (!empty($item['PREVIEW_PICTURE']['SRC'])) {
            $imgSrc = (string)$item['PREVIEW_PICTURE']['SRC'];
        }

        $haveOffers = !empty($item['OFFERS']);
        if ($haveOffers) {
            $actualItem = $item['OFFERS'][$item['OFFERS_SELECTED']] ?? reset($item['OFFERS']);
        } else {
            $actualItem = $item;
        }

        $printPrice = '';
        $printOld = '';
        if (!empty($actualItem['ITEM_PRICES']) && isset($actualItem['ITEM_PRICE_SELECTED'])) {
            $sel = $actualItem['ITEM_PRICE_SELECTED'];
            if (!empty($actualItem['ITEM_PRICES'][$sel])) {
                $pr = $actualItem['ITEM_PRICES'][$sel];
                $printPrice = (string)($pr['PRINT_PRICE'] ?? '');
                $base = (float)($pr['BASE_PRICE'] ?? 0);
                $cur = (float)($pr['PRICE'] ?? 0);
                if ($base > $cur && !empty($pr['PRINT_BASE_PRICE'])) {
                    $printOld = (string)$pr['PRINT_BASE_PRICE'];
                }
            }
        }
        ?>
        <div class="grid__item gird_item grid__item__wrapper">
            <a class="campaign-look-card grid__item__link" href="<?= htmlspecialcharsbx($detailUrl) ?>">
                <span class="campaign-look-card__img-wrap">
                    <?php if ($imgSrc !== ''): ?>
                        <img class="campaign-look-card__img grid__item__image" src="<?= htmlspecialcharsbx($imgSrc) ?>"
                             alt="<?= htmlspecialcharsbx($item['NAME']) ?>">
                    <?php endif; ?>
                </span>
                <div class="campaign-look-card__name grid__item__title"><?= htmlspecialcharsbx($item['NAME']) ?></div>
                <div class="campaign-look-card__prices grid__item__prices">
                    <?php if ($printPrice !== ''): ?>
                        <div class="campaign-look-card__price grid__item__price"><?= $printPrice ?></div>
                    <?php endif; ?>
                    <?php if ($printOld !== ''): ?>
                        <div class="campaign-look-card__price_old grid__item__discount"><?= $printOld ?></div>
                    <?php endif; ?>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
