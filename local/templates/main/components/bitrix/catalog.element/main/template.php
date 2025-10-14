<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Bitrix\Main\Localization\Loc;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

$this->setFrameMode(true);

$templateLibrary = array('popup', 'fx');
$currencyList = '';

if (!empty($arResult['CURRENCIES'])) {
    $templateLibrary[] = 'currency';
    $currencyList = CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true);
}

$templateData = array(
    'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
    'TEMPLATE_LIBRARY' => $templateLibrary,
    'CURRENCIES' => $currencyList,
    'ITEM' => array(
        'ID' => $arResult['ID'],
        'IBLOCK_ID' => $arResult['IBLOCK_ID'],
        'OFFERS_SELECTED' => $arResult['OFFERS_SELECTED'],
        'JS_OFFERS' => $arResult['JS_OFFERS']
    )
);
unset($currencyList, $templateLibrary);

$mainId = $this->GetEditAreaId($arResult['ID']);
$itemIds = array(
    'ID' => $mainId,
    'DISCOUNT_PERCENT_ID' => $mainId . '_dsc_pict',
    'STICKER_ID' => $mainId . '_sticker',
    'BIG_SLIDER_ID' => $mainId . '_big_slider',
    'BIG_IMG_CONT_ID' => $mainId . '_bigimg_cont',
    'SLIDER_CONT_ID' => $mainId . '_slider_cont',
    'OLD_PRICE_ID' => $mainId . '_old_price',
    'PRICE_ID' => $mainId . '_price',
    'DISCOUNT_PRICE_ID' => $mainId . '_price_discount',
    'PRICE_TOTAL' => $mainId . '_price_total',
    'SLIDER_CONT_OF_ID' => $mainId . '_slider_cont_',
    'QUANTITY_ID' => $mainId . '_quantity',
    'QUANTITY_DOWN_ID' => $mainId . '_quant_down',
    'QUANTITY_UP_ID' => $mainId . '_quant_up',
    'QUANTITY_MEASURE' => $mainId . '_quant_measure',
    'QUANTITY_LIMIT' => $mainId . '_quant_limit',
    'BUY_LINK' => $mainId . '_buy_link',
    'ADD_BASKET_LINK' => $mainId . '_add_basket_link',
    'BASKET_ACTIONS_ID' => $mainId . '_basket_actions',
    'NOT_AVAILABLE_MESS' => $mainId . '_not_avail',
    'COMPARE_LINK' => $mainId . '_compare_link',
    'TREE_ID' => $mainId . '_skudiv',
    'DISPLAY_PROP_DIV' => $mainId . '_sku_prop',
    'DISPLAY_MAIN_PROP_DIV' => $mainId . '_main_sku_prop',
    'OFFER_GROUP' => $mainId . '_set_group_',
    'BASKET_PROP_DIV' => $mainId . '_basket_prop',
    'SUBSCRIBE_LINK' => $mainId . '_subscribe',
    'TABS_ID' => $mainId . '_tabs',
    'TAB_CONTAINERS_ID' => $mainId . '_tab_containers',
    'SMALL_CARD_PANEL_ID' => $mainId . '_small_card_panel',
    'TABS_PANEL_ID' => $mainId . '_tabs_panel'
);
$obName = $templateData['JS_OBJ'] = 'ob' . preg_replace('/[^a-zA-Z0-9_]/', 'x', $mainId);
$name = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
    : $arResult['NAME'];
$title = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE']
    : $arResult['NAME'];
$alt = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT']
    : $arResult['NAME'];

$haveOffers = !empty($arResult['OFFERS']);
$actualItem = $arResult["ACTUAL_ITEM"];

$skuProps = array();

$arResult["MORE_PHOTO_COUNT"] = count($actualItem["MORE_PHOTO"]);

$price = $actualItem['ITEM_PRICES'][$actualItem['ITEM_PRICE_SELECTED']];
$price["PRICE_DOLLAR"] = \Level44\Base::getDollarPrice(
    $price['PRICE'],
    $arResult["DISPLAY_PROPERTIES"]['PRICE_DOLLAR']["DISPLAY_VALUE"],
    true
);
$price["PRICE_DOLLAR_FORMATTED"] = \Level44\Base::getDollarPrice(
    $price['PRICE'],
    $arResult["DISPLAY_PROPERTIES"]['PRICE_DOLLAR']["DISPLAY_VALUE"]
);
$price = array_merge($price, $arParams["ECOMMERCE_DATA"]["prices"]);
$measureRatio = $actualItem['ITEM_MEASURE_RATIOS'][$actualItem['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'];
$showDiscount = $price['PERCENT'] > 0;

$showDescription = !empty($arResult['PREVIEW_TEXT']) || !empty($arResult['DETAIL_TEXT']);
$showBuyBtn = in_array('BUY', $arParams['ADD_TO_BASKET_ACTION']);
$buyButtonClassName = in_array('BUY', $arParams['ADD_TO_BASKET_ACTION_PRIMARY']) ? 'btn-default' : 'btn-link';
$showAddBtn = in_array('ADD', $arParams['ADD_TO_BASKET_ACTION']);
$showButtonClassName = in_array('ADD', $arParams['ADD_TO_BASKET_ACTION_PRIMARY']) ? 'btn-default' : 'btn-link';
$showSubscribe = $arParams['PRODUCT_SUBSCRIPTION'] === 'Y' && ($arResult['PRODUCT']['SUBSCRIBE'] === 'Y' || $haveOffers);

$arParams['MESS_BTN_BUY'] = $arParams['MESS_BTN_BUY'] ?: Loc::getMessage('CT_BCE_CATALOG_BUY');
$arParams['MESS_BTN_ADD_TO_BASKET'] = $arParams['MESS_BTN_ADD_TO_BASKET'] ?: Loc::getMessage('CT_BCE_CATALOG_ADD');
$arParams['MESS_NOT_AVAILABLE'] = $arParams['MESS_NOT_AVAILABLE'] ?: Loc::getMessage('CT_BCE_CATALOG_NOT_AVAILABLE');
$arParams['MESS_BTN_COMPARE'] = $arParams['MESS_BTN_COMPARE'] ?: Loc::getMessage('CT_BCE_CATALOG_COMPARE');
$arParams['MESS_PRICE_RANGES_TITLE'] = $arParams['MESS_PRICE_RANGES_TITLE'] ?: Loc::getMessage('CT_BCE_CATALOG_PRICE_RANGES_TITLE');
$arParams['MESS_DESCRIPTION_TAB'] = $arParams['MESS_DESCRIPTION_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_DESCRIPTION_TAB');
$arParams['MESS_PROPERTIES_TAB'] = $arParams['MESS_PROPERTIES_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_PROPERTIES_TAB');
$arParams['MESS_COMMENTS_TAB'] = $arParams['MESS_COMMENTS_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_COMMENTS_TAB');
$arParams['MESS_SHOW_MAX_QUANTITY'] = $arParams['MESS_SHOW_MAX_QUANTITY'] ?: Loc::getMessage('CT_BCE_CATALOG_SHOW_MAX_QUANTITY');
$arParams['MESS_RELATIVE_QUANTITY_MANY'] = $arParams['MESS_RELATIVE_QUANTITY_MANY'] ?: Loc::getMessage('CT_BCE_CATALOG_RELATIVE_QUANTITY_MANY');
$arParams['MESS_RELATIVE_QUANTITY_FEW'] = $arParams['MESS_RELATIVE_QUANTITY_FEW'] ?: Loc::getMessage('CT_BCE_CATALOG_RELATIVE_QUANTITY_FEW');

$positionClassMap = array(
    'left' => 'product-item-label-left',
    'center' => 'product-item-label-center',
    'right' => 'product-item-label-right',
    'bottom' => 'product-item-label-bottom',
    'middle' => 'product-item-label-middle',
    'top' => 'product-item-label-top'
);

$discountPositionClass = 'product-item-label-big';
if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y' && !empty($arParams['DISCOUNT_PERCENT_POSITION'])) {
    foreach (explode('-', $arParams['DISCOUNT_PERCENT_POSITION']) as $pos) {
        $discountPositionClass .= isset($positionClassMap[$pos]) ? ' ' . $positionClassMap[$pos] : '';
    }
}

$labelPositionClass = 'product-item-label-big';
if (!empty($arParams['LABEL_PROP_POSITION'])) {
    foreach (explode('-', $arParams['LABEL_PROP_POSITION']) as $pos) {
        $labelPositionClass .= isset($positionClassMap[$pos]) ? ' ' . $positionClassMap[$pos] : '';
    }
}

global $USER;


Bitrix\Main\Loader::includeModule('awelite.favorite');
$defaultClass = \Bitrix\Main\Config\Option::get('awelite.favorite', 'removeClass');

?>

<? if (!empty($actualItem['MORE_PHOTO'])): ?>
    <div class="product__slider-mobile">
        <div class="embla" data-mouse-scroll="false" data-loop="true" data-autoplay="false">
            <div class="embla__container">
                <? foreach ($actualItem['MORE_PHOTO'] as $item): ?>
                    <? if ($item['IS_VIDEO']): ?>
                        <div class="embla__slide">
                            <div class="embla__slide-content">
                                <div class="product__video-wrapper">
                                    <div class="video-image">
                                        <img class="img-fluid" src="<?= $item['PREVIEW_SRC'] ?>" alt="">
                                        <svg class="icon icon-play video-play-icon">
                                            <use xlink:href="#play"></use>
                                        </svg>
                                    </div>
                                    <video class="product__video mobile" loop playsinline>
                                        <source src="<?= $item['SRC'] ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            </div>
                        </div>
                    <? else: ?>
                        <div class="embla__slide">
                            <div class="embla__slide-content">
                                <img class="img-fluid" src="<?= $item['SRC'] ?>" alt="">
                            </div>
                        </div>
                    <? endif; ?>
                <? endforeach; ?>
            </div>
            <div class="embla__dots">
                <? foreach ($actualItem['MORE_PHOTO'] as $index => $item): ?>
                    <button type="button" data-index="<?= $index ?>">
                        <div class="button-body"></div>
                    </button>
                <? endforeach; ?>
            </div>
        </div>
    </div>
<? endif; ?>
    <div class="product__body" id="<?= $itemIds['ID'] ?>">
        <? if (!empty($actualItem['MORE_PHOTO'])): ?>
            <div class="product__slider-desktop">
                <div class="previews">
                    <? foreach ($actualItem['MORE_PHOTO'] as $index => $item): ?>
                        <? if ($item['IS_VIDEO']): ?>
                            <a class="preview-link with-video" href="#image-<?= $index ?>" data-index="<?= $index ?>">
                                <svg class="icon icon-play video-play-icon">
                                    <use xlink:href="#play"></use>
                                </svg>
                                <img class="img-fluid" src="<?= $item['POSTER_SRC'] ?>" alt="">
                            </a>
                        <? else: ?>
                            <a class="preview-link" href="#image-<?= $index ?>" data-index="<?= $index ?>">
                                <img class="img-fluid" src="<?= $item['SRC'] ?>" alt="">
                            </a>
                        <? endif; ?>
                    <? endforeach; ?>
                </div>
                <div class="images">
                    <? foreach ($actualItem['MORE_PHOTO'] as $index => $item): ?>
                        <? if ($item['IS_VIDEO']): ?>
                            <div class="image-wrapper" id="image-<?= $index ?>">
                                <div class="product__video-wrapper">
                                    <div class="video-image">
                                        <img class="img-fluid" src="<?= $item['POSTER_SRC'] ?>" alt="">
                                        <svg class="icon icon-play video-play-icon">
                                            <use xlink:href="#play"></use>
                                        </svg>
                                    </div>
                                    <video class="product__video" loop playsinline>
                                        <source src="<?= $item['SRC'] ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            </div>
                        <? else: ?>
                            <div class="image-wrapper" id="image-<?= $index ?>">
                                <img class="img-fluid" src="<?= $item['SRC'] ?>" alt="">
                            </div>
                        <? endif; ?>
                    <? endforeach; ?>
                </div>
            </div>
        <? endif; ?>

        <div class="product__info">
            <div>
                <h1 class="product__title"><?= $name ?></h1>
                <div class="product__price-wrapper">
                    <div class="product__price" id="<?= $itemIds['PRICE_ID'] ?>"><?= $price['PRINT_PRICE'] ?></div>
                    <? if (!empty($price["oldPrice"])): ?>
                        <div class="product__price discount" id="<?= $itemIds['PRICE_ID'] ?>">
                            <?= $price["oldPriceFormat"] ?>
                        </div>
                    <? endif; ?>
                </div>
                <? if ($price["PRICE_DOLLAR"]): ?>
                    <div class="product__price-wrapper">
                        <div class="product__price"
                             id="<?= $itemIds['PRICE_ID'] ?>"><?= $price['PRICE_DOLLAR_FORMATTED'] ?></div>
                        <? if (!empty($price["oldPriceDollar"])): ?>
                            <div class="product__price discount" id="<?= $itemIds['PRICE_ID'] ?>">
                                <?= $price["oldPriceDollarFormat"] ?>
                            </div>
                        <? endif; ?>
                    </div>
                <? endif; ?>
            </div>

            <?//if (in_array(1, $USER->GetUserGroupArray())){?>
            <a class="dolyame-text" href="#" data-toggle="modal" data-target="#dolyame-modal">4 платежа по <?=round($price['RATIO_PRICE']/4)?> ₽   ></a>

            <? if ($price["PRICE_DOLLAR"]): ?>
                <a class="dolyame-text" href="#" data-toggle="modal" data-target="#dolyame-modal">4 payments  <?=round($price['PRICE_DOLLAR']/4)?> $ ></a>
            <? endif; ?>

            <?//}?>

            <? if (!empty($arResult["COLORS"])): ?>
                <div class="product__color color">
                    <div class="color__group btn-group-toggle" data-toggle="buttons">
                        <? foreach ($arResult["COLORS"] as $index => $item): ?>
                            <? if ($item["ACTIVE"]): ?>
                                <label class="btn color__btn js-color__btn active" title="<?= $item['COLOR_NAME'] ?>">
                                    <input id="color<?= $index ?>" type="radio" autocomplete="off" name="color" checked>
                                    <span class="color__value"
                                          style="background-image: url('<?= $item["COLOR"]['UF_FILE'] ?>');">
                                    </span>
                                </label>
                            <? else: ?>
                                <label class="btn color__btn js-color__btn" title="<?= $item['COLOR_NAME'] ?>">
                                    <input id="color<?= $index ?>" type="radio" autocomplete="off" name="color">
                                    <a href="<?= $item["DETAIL_PAGE_URL"] ?>">
                                        <span class="color__value"
                                              style="background-image: url('<?= $item["COLOR"]['UF_FILE'] ?>');">
                                        </span>
                                    </a>
                                </label>
                            <? endif; ?>
                        <? endforeach; ?>
                    </div>
                    <? if (!empty($arResult["COLOR_NAME"])): ?>
                        <div class="color__title js-color__name"><?= $arResult["COLOR_NAME"] ?></div>
                    <? endif; ?>
                </div>
            <? endif; ?>
            <?
            if ($haveOffers && !empty($arResult['OFFERS_PROP'])): ?>
                <?
                foreach ($arResult['SKU_PROPS'] as $skuProperty): ?>
                    <? if (!isset($arResult['OFFERS_PROP'][$skuProperty['CODE']])):
                        continue;
                        ?>
                    <?endif;
                    $skuProps[] = [
                        'ID'           => $skuProperty['ID'],
                        'SHOW_MODE'    => $skuProperty['SHOW_MODE'],
                        'VALUES'       => $skuProperty['VALUES'],
                        'VALUES_COUNT' => $skuProperty['VALUES_COUNT']
                    ];
                    ?>
                    <?
                    if ($skuProperty['CODE'] === "SIZE_REF"): ?>
                        <div>
                            <div class="dimension" id="<?= $itemIds['TREE_ID'] ?>">
                                <div class="dimension__header">
                                    <div class="title"><?= Loc::getMessage('SIZE') ?></div>
                                    <!-- Button modal-->
                                    <?if (strpos($APPLICATION->GetCurPage(),'obuv')===false){?>
                                    <button class="btn table-btn dimension__table-btn" type="button" data-toggle="modal"
                                            data-target="#dimension__table-modal">
                                        <?= Loc::getMessage('SIZE_TABLE') ?>
                                    </button>
                                    <?}?>
                                </div>
                                <div class="dimension__group btn-group-toggle" data-toggle="buttons"
                                     data-entity="sku-line-block">
                                    <? foreach ($skuProperty['VALUES'] as $index => $value): ?>
                                        <label class="btn dimension__btn"
                                               data-treevalue="<?= $skuProperty['ID'] ?>_<?= $value['ID'] ?>"
                                               data-onevalue="<?= $value['ID'] ?>"

                                        >
                                            <input id="dimension<?= $index ?>" type="radio" name="dimension"
                                                   autocomplete="off">
                                            <?= $value['NAME'] ?>
                                        </label>
                                    <? endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <? endif; ?>
                <? endforeach; ?>
            <? endif; ?>

            <div class="new-btn-wrap-fav">
            <div class="<?= $itemIds['BASKET_ACTIONS_ID'] ?>"
                 style="width:100%;display: <?= ($actualItem['CAN_BUY'] ? '' : 'none') ?>;">
                <button class="btn btn-dark btn-block js-btn__add-basket product__add-basket-desktop <?= $itemIds['ADD_BASKET_LINK'] ?>"
                        data-added-text="<?= Loc::getMessage("ADDED_TO_BASKET") ?>"
                        type="submit">
                    <?= Loc::getMessage("ADD_TO_BASKET") ?>
                </button>
            </div>

            <button class="btn btn-dark btn-block js-btn__add-basket product__add-basket-desktop <?= $itemIds['NOT_AVAILABLE_MESS'] ?>"
                    type="submit"
                    onclick="return false;"
                    style="display: <?= (!$actualItem['CAN_BUY'] ? '' : 'none') ?>"
                    disabled
            >
                <?= Loc::getMessage("NOT_AVAILABLE") ?>
            </button>

            <div class="favorite-detail-wrap">
                <button class="btn btn-link grid__item__favoritedetail js-favorite <?= $defaultClass ?>"
                        onClick="BX.Awelite.changeToFavorite(this);return false;"
                        data-favorite-entity="<?= $arResult['ID'] ?>"
                        data-iblock-id="<?= $arResult['IBLOCK_ID'] ?>">
                    <svg class="icon icon-favorites-add grid__item__favorite__icon">
                        <use xlink:href="#favorites-add"></use>
                    </svg>
                    <svg class="icon icon-favorites grid__item__favorite__icon-active">
                        <use xlink:href="#favorites"></use>
                    </svg>
                </button>

            </div>
        </div>



            <div class="js-subscribe-buttons"
                 style="display:<?= !$actualItem["CAN_BUY"] ? "block" : "none" ?>;"
            ></div>
            <button type="button"
                    class="modal-toggle"
                    style="display:none;"
            ></button>

            <? if (!empty($arResult['DETAIL_TEXT']) || !empty($arResult['ARTNUMBER']) || !empty($arResult["PRODUCT_COMPOSITION"])): ?>
                <div class="product__desc">
                    <div class="title"><?= Loc::getMessage('CT_BCE_CATALOG_DESCRIPTION') ?></div>
                    <div class="body">
                        <? if (!empty($arResult['ARTNUMBER'])): ?>
                            <p><?= Loc::getMessage('ARTICLE') ?>: <?= $arResult['ARTNUMBER'] ?></p>
                        <? endif; ?>
                        <? if (!empty($arResult['DETAIL_TEXT'])): ?>
                            <? if ($arResult['DETAIL_TEXT_TYPE'] === 'html'): ?>
                                <?= $arResult['DETAIL_TEXT'] ?>
                            <? else: ?>
                                <p><?= $arResult['DETAIL_TEXT'] ?></p>
                            <? endif; ?>
                        <? endif; ?>
                        <? if (!empty($arResult["PRODUCT_COMPOSITION"])): ?>
                            <p><?= Loc::getMessage('COMPOSITION') ?>: <?= $arResult["PRODUCT_COMPOSITION"] ?></p>
                        <? endif; ?>
                    </div>
                </div>
            <? endif; ?>
            <div class="product__accordions">
                <? if (!empty($arResult['STORES'])): ?>
                    <div class="accordion">
                        <button class="btn btn-link accordion__trigger" type="button" aria-label="Toggle accordion">
                            <div class="accordion__title"><?= Loc::getMessage('AVAILABILITY') ?></div>
                            <svg class="icon icon-arrow-down accordion__icon">
                                <use xlink:href="#arrow-down"></use>
                            </svg>
                        </button>
                        <div class="accordion__content">
                            <div class="product__availability">
                                <? foreach ($arResult['STORES'] as $store): ?>
                                    <div class="item">
                                        <? if (!empty($store['TITLE'])): ?>
                                            <div class="title"><?= $store['TITLE'] ?></div>
                                        <? endif; ?>
                                        <div class="grid">
                                            <div class="address"><?= $store['ADDRESS'] ?></div>
                                            <div class="sizes">
                                                <? foreach ($store['SIZES'] as $size): ?>
                                                    <? if (!empty($size['NAME'])): ?>
                                                        <div class="size"><?= $size['NAME'] ?></div>
                                                    <? endif; ?>
                                                <? endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <? endforeach; ?>
                            </div>
                        </div>
                    </div>
                <? endif; ?>
                <? if (!empty($arResult["MEASUREMENTS"])): ?>
                    <div class="accordion">
                        <button class="btn btn-link accordion__trigger" type="button" aria-label="Toggle accordion">
                            <div class="accordion__title"><?= Loc::getMessage('MEASUREMENTS') ?></div>
                            <svg class="icon icon-arrow-down accordion__icon">
                                <use xlink:href="#arrow-down"></use>
                            </svg>
                        </button>
                        <div class="accordion__content">
                            <div class="body">
                                <?= $arResult["MEASUREMENTS"] ?>
                            </div>
                        </div>
                    </div>
                <? endif; ?>
                <? if (!empty($arResult["CARE_INFO"])): ?>
                    <div class="accordion">
                        <button class="btn btn-link accordion__trigger" type="button" aria-label="Toggle accordion">
                            <div class="accordion__title"><?= Loc::getMessage('CARE') ?></div>
                            <svg class="icon icon-arrow-down accordion__icon">
                                <use xlink:href="#arrow-down"></use>
                            </svg>
                        </button>
                        <div class="accordion__content">
                            <div class="body">
                                <?= $arResult["CARE_INFO"] ?>
                            </div>
                        </div>
                    </div>
                <?endif; ?>
            </div>
        </div>
    </div>
    <div class="product__add-basket-mobile">
        <div class="<?= $itemIds['BASKET_ACTIONS_ID'] ?>"
             style="display: <?= ($actualItem['CAN_BUY'] ? '' : 'none') ?>;">
            <button class="btn btn-dark btn-block js-btn__add-basket <?= $itemIds['ADD_BASKET_LINK'] ?>"
                    data-added-text="<?= Loc::getMessage("ADDED_TO_BASKET") ?>"
                    type="submit">
                <?= Loc::getMessage("ADD_TO_BASKET") ?>
            </button>
        </div>

        <button class="btn btn-dark btn-block js-btn__add-basket <?= $itemIds['NOT_AVAILABLE_MESS'] ?>"
                type="submit"
                onclick="return false;"
                style="display: <?= (!$actualItem['CAN_BUY'] ? '' : 'none') ?>"
                disabled
        >
            <?= Loc::getMessage("NOT_AVAILABLE") ?>
        </button>
    </div>
<?
if ($haveOffers) {
    $offerIds = array();
    $offerCodes = array();

    $useRatio = $arParams['USE_RATIO_IN_RANGES'] === 'Y';

    foreach ($arResult['JS_OFFERS'] as $ind => &$jsOffer) {
        $offerIds[] = (int)$jsOffer['ID'];
        $offerCodes[] = $jsOffer['CODE'];

        $fullOffer = $arResult['OFFERS'][$ind];
        $measureName = $fullOffer['ITEM_MEASURE']['TITLE'];

        $strAllProps = '';
        $strMainProps = '';
        $strPriceRangesRatio = '';
        $strPriceRanges = '';

        if ($arResult['SHOW_OFFERS_PROPS']) {
            if (!empty($jsOffer['DISPLAY_PROPERTIES'])) {
                foreach ($jsOffer['DISPLAY_PROPERTIES'] as $property) {
                    $current = '<dt>' . $property['NAME'] . '</dt><dd>' . (
                        is_array($property['VALUE'])
                            ? implode(' / ', $property['VALUE'])
                            : $property['VALUE']
                        ) . '</dd>';
                    $strAllProps .= $current;

                    if (isset($arParams['MAIN_BLOCK_OFFERS_PROPERTY_CODE'][$property['CODE']])) {
                        $strMainProps .= $current;
                    }
                }

                unset($current);
            }
        }

        if ($arParams['USE_PRICE_COUNT'] && count($jsOffer['ITEM_QUANTITY_RANGES']) > 1) {
            $strPriceRangesRatio = '(' . Loc::getMessage(
                    'CT_BCE_CATALOG_RATIO_PRICE',
                    array(
                        '#RATIO#' => ($useRatio
                                ? $fullOffer['ITEM_MEASURE_RATIOS'][$fullOffer['ITEM_MEASURE_RATIO_SELECTED']]['RATIO']
                                : '1'
                            ) . ' ' . $measureName
                    )
                ) . ')';

            foreach ($jsOffer['ITEM_QUANTITY_RANGES'] as $range) {
                if ($range['HASH'] !== 'ZERO-INF') {
                    $itemPrice = false;

                    foreach ($jsOffer['ITEM_PRICES'] as $itemPrice) {
                        if ($itemPrice['QUANTITY_HASH'] === $range['HASH']) {
                            break;
                        }
                    }

                    if ($itemPrice) {
                        $strPriceRanges .= '<dt>' . Loc::getMessage(
                                'CT_BCE_CATALOG_RANGE_FROM',
                                array('#FROM#' => $range['SORT_FROM'] . ' ' . $measureName)
                            ) . ' ';

                        if (is_infinite($range['SORT_TO'])) {
                            $strPriceRanges .= Loc::getMessage('CT_BCE_CATALOG_RANGE_MORE');
                        } else {
                            $strPriceRanges .= Loc::getMessage(
                                'CT_BCE_CATALOG_RANGE_TO',
                                array('#TO#' => $range['SORT_TO'] . ' ' . $measureName)
                            );
                        }

                        $strPriceRanges .= '</dt><dd>' . ($useRatio ? $itemPrice['PRINT_RATIO_PRICE'] : $itemPrice['PRINT_PRICE']) . '</dd>';
                    }
                }
            }

            unset($range, $itemPrice);
        }

        $jsOffer['DISPLAY_PROPERTIES'] = $strAllProps;
        $jsOffer['DISPLAY_PROPERTIES_MAIN_BLOCK'] = $strMainProps;
        $jsOffer['PRICE_RANGES_RATIO_HTML'] = $strPriceRangesRatio;
        $jsOffer['PRICE_RANGES_HTML'] = $strPriceRanges;
    }

    $templateData['OFFER_IDS'] = $offerIds;
    $templateData['OFFER_CODES'] = $offerCodes;
    unset($jsOffer, $strAllProps, $strMainProps, $strPriceRanges, $strPriceRangesRatio, $useRatio);

    $jsParams = array(
        'CONFIG' => array(
            'USE_CATALOG' => $arResult['CATALOG'],
            'SHOW_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
            'SHOW_PRICE' => true,
            'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'] === 'Y',
            'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'] === 'Y',
            'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
            'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
            'SHOW_SKU_PROPS' => $arResult['SHOW_OFFERS_PROPS'],
            'OFFER_GROUP' => $arResult['OFFER_GROUP'],
            'MAIN_PICTURE_MODE' => $arParams['DETAIL_PICTURE_MODE'],
            'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
            'SHOW_CLOSE_POPUP' => $arParams['SHOW_CLOSE_POPUP'] === 'Y',
            'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
            'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
            'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
            'USE_STICKERS' => true,
            'USE_SUBSCRIBE' => $showSubscribe,
            'SHOW_SLIDER' => $arParams['SHOW_SLIDER'],
            'SLIDER_INTERVAL' => $arParams['SLIDER_INTERVAL'],
            'ALT' => $alt,
            'TITLE' => $title,
            'MAGNIFIER_ZOOM_PERCENT' => 200,
            'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
            'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
            'BRAND_PROPERTY' => !empty($arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']])
                ? $arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']]['DISPLAY_VALUE']
                : null
        ),
        'PRODUCT_TYPE' => $arResult['PRODUCT']['TYPE'],
        'VISUAL' => $itemIds,
        'DEFAULT_PICTURE' => array(
            'PREVIEW_PICTURE' => $arResult['DEFAULT_PICTURE'],
            'DETAIL_PICTURE' => $arResult['DEFAULT_PICTURE']
        ),
        'PRODUCT' => array(
            'ID' => $arResult['ID'],
            'ACTIVE' => $arResult['ACTIVE'],
            'NAME' => $arResult['~NAME'],
            'CATEGORY' => $arResult['CATEGORY_PATH']
        ),
        'BASKET' => array(
            'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
            'BASKET_URL' => $arParams['BASKET_URL'],
            'SKU_PROPS' => $arResult['OFFERS_PROP_CODES'],
            'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
            'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
        ),
        'OFFERS' => $arResult['JS_OFFERS'],
        'OFFER_SELECTED' => $arResult['OFFERS_SELECTED'],
        'TREE_PROPS' => $skuProps
    );
} else {

    $jsParams = array(
        'CONFIG' => array(
            'USE_CATALOG' => $arResult['CATALOG'],
            'SHOW_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
            'SHOW_PRICE' => !empty($arResult['ITEM_PRICES']),
            'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'] === 'Y',
            'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'] === 'Y',
            'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
            'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
            'MAIN_PICTURE_MODE' => $arParams['DETAIL_PICTURE_MODE'],
            'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
            'SHOW_CLOSE_POPUP' => $arParams['SHOW_CLOSE_POPUP'] === 'Y',
            'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
            'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
            'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
            'USE_STICKERS' => true,
            'USE_SUBSCRIBE' => $showSubscribe,
            'SHOW_SLIDER' => $arParams['SHOW_SLIDER'],
            'SLIDER_INTERVAL' => $arParams['SLIDER_INTERVAL'],
            'ALT' => $alt,
            'TITLE' => $title,
            'MAGNIFIER_ZOOM_PERCENT' => 200,
            'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
            'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
            'BRAND_PROPERTY' => !empty($arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']])
                ? $arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']]['DISPLAY_VALUE']
                : null
        ),
        'VISUAL' => $itemIds,
        'PRODUCT_TYPE' => $arResult['PRODUCT']['TYPE'],
        'PRODUCT' => array(
            'ID' => $arResult['ID'],
            'ACTIVE' => $arResult['ACTIVE'],
            'PICT' => reset($arResult['MORE_PHOTO']),
            'NAME' => $arResult['~NAME'],
            'SUBSCRIPTION' => true,
            'ITEM_PRICE_MODE' => $arResult['ITEM_PRICE_MODE'],
            'ITEM_PRICES' => $arResult['ITEM_PRICES'],
            'ITEM_PRICE_SELECTED' => $arResult['ITEM_PRICE_SELECTED'],
            'ITEM_QUANTITY_RANGES' => $arResult['ITEM_QUANTITY_RANGES'],
            'ITEM_QUANTITY_RANGE_SELECTED' => $arResult['ITEM_QUANTITY_RANGE_SELECTED'],
            'ITEM_MEASURE_RATIOS' => $arResult['ITEM_MEASURE_RATIOS'],
            'ITEM_MEASURE_RATIO_SELECTED' => $arResult['ITEM_MEASURE_RATIO_SELECTED'],
            'SLIDER_COUNT' => $arResult['MORE_PHOTO_COUNT'],
            'SLIDER' => $arResult['MORE_PHOTO'],
            'CAN_BUY' => $arResult['CAN_BUY'],
            'CHECK_QUANTITY' => $arResult['CHECK_QUANTITY'],
            'QUANTITY_FLOAT' => is_float($arResult['ITEM_MEASURE_RATIOS'][$arResult['ITEM_MEASURE_RATIO_SELECTED']]['RATIO']),
            'MAX_QUANTITY' => $arResult['PRODUCT']['QUANTITY'],
            'STEP_QUANTITY' => $arResult['ITEM_MEASURE_RATIOS'][$arResult['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'],
            'CATEGORY' => $arResult['CATEGORY_PATH']
        ),
        'BASKET' => array(
            'ADD_PROPS' => $arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y',
            'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
            'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE'],
            'EMPTY_PROPS' => $emptyProductProperties,
            'BASKET_URL' => $arParams['BASKET_URL'],
            'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
            'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
        )
    );
    unset($emptyProductProperties);
}

if ($arParams['DISPLAY_COMPARE']) {
    $jsParams['COMPARE'] = array(
        'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
        'COMPARE_DELETE_URL_TEMPLATE' => $arResult['~COMPARE_DELETE_URL_TEMPLATE'],
        'COMPARE_PATH' => $arParams['COMPARE_PATH']
    );
} ?>



    <div class="modal fade" id="dolyame-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Оплата покупок в рассрочку</h5>

        </div>
        <div class="modal-body">
            <img src="/local/templates/.default/assets/img/dolyame.svg" style="width:148px;"/>

            <div class="dolyame-plashka">
                <div class="dolyame-info">25% сейчас, остальное — потом</div>

                <div class="dolyame-points">
                    <div>Сегодня</div>
                    <div>Через 2 недели</div>
                    <div>Через 4 недели</div>
                    <div>Через 6 недель</div>
                </div>
            </div>

            <button class="modal-close dolyame-close">Закрыть</button>
        </div>
    </div>
    </div>
    </div>





    <script>
        BX.message({
            ECONOMY_INFO_MESSAGE: '<?=GetMessageJS('CT_BCE_CATALOG_ECONOMY_INFO2')?>',
            TITLE_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_TITLE_ERROR')?>',
            TITLE_BASKET_PROPS: '<?=GetMessageJS('CT_BCE_CATALOG_TITLE_BASKET_PROPS')?>',
            BASKET_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_BASKET_UNKNOWN_ERROR')?>',
            BTN_SEND_PROPS: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_SEND_PROPS')?>',
            BTN_MESSAGE_BASKET_REDIRECT: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_BASKET_REDIRECT')?>',
            BTN_MESSAGE_CLOSE: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE')?>',
            BTN_MESSAGE_CLOSE_POPUP: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE_POPUP')?>',
            TITLE_SUCCESSFUL: '<?=GetMessageJS('CT_BCE_CATALOG_ADD_TO_BASKET_OK')?>',
            COMPARE_MESSAGE_OK: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_OK')?>',
            COMPARE_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_UNKNOWN_ERROR')?>',
            COMPARE_TITLE: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_TITLE')?>',
            BTN_MESSAGE_COMPARE_REDIRECT: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_COMPARE_REDIRECT')?>',
            PRODUCT_GIFT_LABEL: '<?=GetMessageJS('CT_BCE_CATALOG_PRODUCT_GIFT_LABEL')?>',
            PRICE_TOTAL_PREFIX: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_PRICE_TOTAL_PREFIX')?>',
            SUBSCRIBE_INVALID_EMAIL: '<?=GetMessageJS('SUBSCRIBE_INVALID_EMAIL')?>',
            SUBSCRIBE_INVALID_PHONE: '<?=GetMessageJS('SUBSCRIBE_INVALID_PHONE')?>',
            SUBSCRIBE_INTERNAL_ERROR: '<?=GetMessageJS('SUBSCRIBE_INTERNAL_ERROR')?>',
            RELATIVE_QUANTITY_MANY: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_MANY'])?>',
            RELATIVE_QUANTITY_FEW: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_FEW'])?>',
            SITE_ID: '<?=CUtil::JSEscape($component->getSiteId())?>'
        });

        var <?=$obName?> =
        new JCCatalogElement(<?=CUtil::PhpToJSObject($jsParams, false, true)?>);
    </script>
<?
unset($actualItem, $itemIds, $jsParams);
