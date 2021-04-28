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


<a class="catalog__item-image" href="<?= $item['DETAIL_PAGE_URL'] ?>">
    <img class="img-fluid" src="<?= $item['PREVIEW_PICTURE']['SRC'] ?>" alt="<?= $item['NAME'] ?>">
</a>
<div class="catalog__item-footer">
    <a class="catalog__item-title" href="<?= $item['DETAIL_PAGE_URL'] ?>"><?= $item['NAME'] ?></a>
    <div>
      <span class="catalog__item-price"><?= $price['PRINT_PRICE'] ?></span>
        <? if ($price["PRICE_DOLLAR"]): ?>
            &middot;
            <span class="catalog__item-price"><?= $price["PRICE_DOLLAR"] ?></span>
        <? endif; ?>
    </div>
    <div class="catalog__item-price-crossed">
      <span class="catalog__item-price"><?= $price['PRINT_PRICE'] ?></span>
        <? if ($price["PRICE_DOLLAR"]): ?>
            &middot;
            <span class="catalog__item-price"><?= $price["PRICE_DOLLAR"] ?></span>
        <? endif; ?>
    </div>
</div>
