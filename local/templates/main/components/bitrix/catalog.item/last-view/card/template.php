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

$soldout=true;

foreach ($item['OFFERS'] as $offer) {

    if ($offer['PRODUCT']['AVAILABLE']=='Y')
    {
        $soldout=false;
        break;
    }
};

Bitrix\Main\Loader::includeModule('awelite.favorite');
$defaultClass = \Bitrix\Main\Config\Option::get('awelite.favorite', 'removeClass');


?>



<a class=" grid__item__link" href="<?= $item['DETAIL_PAGE_URL'] ?>">


                    <img class="grid__item__image" src="<?=$item['DETAIL_PICTURE']['SRC']?>" alt="<?= $item['NAME'] ?>">

    <div class="grid__item__title"><?= $item['NAME'] ?></div>
    <div class="grid__item__prices">
        <div class="grid__item__price"><?= $price['PRINT_PRICE'] ?></div>
        <? if (!empty($price["oldPrice"])): ?>
            <div class="grid__item__discount"><?= $price['oldPriceFormat'] ?></div>
        <? endif; ?>
    </div>

    <? if ($price["PRICE_DOLLAR"]): ?>
        <div class="grid__item__prices">
            <div class="grid__item__price"><?= $price['PRICE_DOLLAR_FORMATTED'] ?></div>
            <? if (!empty($price["oldPriceDollar"])): ?>
                <div class="grid__item__discount"><?= $price["oldPriceDollarFormat"] ?></div>
            <? endif; ?>
        </div>
    <? endif; ?>
</a>
