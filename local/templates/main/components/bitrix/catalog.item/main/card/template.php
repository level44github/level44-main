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

<div class="catalog__scroll">
    <a class="catalog__item-image" href="<?= $item['DETAIL_PAGE_URL'] ?>">
        <? foreach ($item["PREVIEW_IMAGES"] as $previewImage): ?>
                <img class="img-fluid" src="<?= $previewImage ?>" alt="<?= $item['NAME'] ?>">
        <? endforeach; ?>
    </a>
</div>
<div class="catalog__item-footer">
    <a class="catalog__item-title" href="<?= $item['DETAIL_PAGE_URL'] ?>"><?= $item['NAME'] ?></a>
    <div class="<?= $price["oldPrice"] ? "product__final-price" : "" ?>">
      <span class="catalog__item-price"><?= $price['PRINT_PRICE'] ?></span>
        <? if ($price["PRICE_DOLLAR"]): ?>
            &middot;
            <span class="catalog__item-price"><?= $price["PRICE_DOLLAR"] ?></span>
        <? endif; ?>
    </div>
    <? if (!empty($price["oldPrice"])): ?>
        <div class="catalog__item-price-crossed">
            <span class="catalog__item-price"><?= $price['oldPriceFormat'] ?></span>
            <? if ($price["PRICE_DOLLAR"]): ?>
                &middot;
                <span class="catalog__item-price"><?= $price["oldPriceDollarFormat"] ?></span>
            <? endif; ?>
        </div>
    <? endif; ?>
</div>
