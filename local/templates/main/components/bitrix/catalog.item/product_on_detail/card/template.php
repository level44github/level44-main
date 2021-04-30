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

<a class="carousel__image" href="<?= $item['DETAIL_PAGE_URL'] ?>">
    <img class="img-fluid" src="<?= $item['PREVIEW_PICTURE']['SRC'] ?>" alt="<?= $item['NAME'] ?>">
</a>
<div class="carousel__footer">
    <a class="carousel__title" href="<?= $item['DETAIL_PAGE_URL'] ?>"><?= $item['NAME'] ?></a>
    <div><span class="carousel__price"><?= $price['PRINT_PRICE'] ?></span>
        <? if ($item["PRICE_DOLLAR"]): ?>
            &middot; <span class="carousel__price"><?= $item["PRICE_DOLLAR"] ?></span>
        <? endif; ?>
    </div>
    <div class="carousel__price-crossed"><span><?= $price['PRINT_PRICE'] ?></span>
        <? if ($item["PRICE_DOLLAR"]): ?>
            &middot; <span class="carousel__price"><?= $item["PRICE_DOLLAR"] ?></span>
        <? endif; ?>
    </div>
</div>
