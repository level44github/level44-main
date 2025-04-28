<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Bitrix\Main\Localization\Loc;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $item
 * @var array $actualItem
 * @var array $minOffer
 * @var array $itemIds
 * @var array $price
 * @var array $measureRatio
 * @var bool $haveOffers
 * @var bool $showSubscribe
 * @var array $morePhoto
 * @var bool $showSlider
 * @var bool $itemHasDetailUrl
 * @var string $imgTitle
 * @var string $productTitle
 * @var string $buttonSizeClass
 * @var CatalogSectionComponent $component
 */
?>

<div class="grid__item">
    <a class="grid__item__link" href="<?= $item['DETAIL_PAGE_URL'] ?>">
        <img class="grid__item__image" src="<?= $item['PREVIEW_PICTURE']['SRC'] ?>" alt="<?= $item['NAME'] ?>">
        <div class="grid__item__title"><?= $item['NAME'] ?></div>
        <div class="grid__item__prices">
            <div class="grid__item__price"><?= $price['PRINT_PRICE'] ?></div>
            <? if (!empty($price['oldPrice'])): ?>
                <div class="grid__item__discount"><?= $price['oldPriceFormat'] ?></div>
            <? endif; ?>
        </div>

        <? if ($item["PRICE_DOLLAR"]): ?>
            <div class="grid__item__prices">
                <div class="grid__item__price"><?= $item["PRICE_DOLLAR"] ?></div>
                <? if (!empty($price['oldPriceDollar'])): ?>
                    <div class="grid__item__discount"><?= $price['oldPriceDollarFormat'] ?></div>
                <? endif; ?>
            </div>
        <? endif; ?>
    </a>
</div>
