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
?>

    <div class="row product__body" id="<?= $itemIds['ID'] ?>">
        <?
        if (!empty($actualItem['MORE_PHOTO']) || !empty($actualItem['VIDEOS'])): ?>
            <div class="col-lg-7 mb-lg-5 gallery" id="<?= $itemIds['BIG_SLIDER_ID'] ?>">
                <div class="gallery__thumb js-gallery__thumb">
                    <? foreach ($actualItem['MORE_PHOTO'] as $key => $photo): ?>
                        <div class="gallery__thumb-item" data-entity="image" data-id="<?= $photo['ID'] ?>">
                            <img class="img-fluid" src="<?= $photo['SRC'] ?>" alt="<?= $alt ?>">
                        </div>
                    <? endforeach; ?>

                    <? foreach ($arResult['VIDEOS'] as $key => $video): ?>
                        <div class="gallery__thumb-item" data-entity="image" data-id="<?= $video["ID"] ?>">
                            <video>
                                <source class="img-fluid js-gallery__popup-img" src="<?= $video["PATH"] ?>"/>
                            </video>
                        </div>
                    <? endforeach; ?>
                </div>
                <div class="gallery__main">
                    <div class="gallery__picture js-gallery__picture">
                        <? foreach ($actualItem['MORE_PHOTO'] as $key => $photo): ?>
                            <a class="gallery__full" href="#" data-toggle="modal"
                               data-target="#gallery__popup">
                                <img class="img-fluid js-gallery__popup-img" alt="" data-lazy="<?= $photo["SRC"] ?>">
                            </a>
                        <? endforeach; ?>

                        <? foreach ($arResult['VIDEOS'] as $key => $video): ?>
                            <a class="gallery__full" href="#" data-toggle="modal"
                               data-target="#gallery__popup">
                                <video>
                                    <source class="img-fluid js-gallery__popup-img" src="<?= $video["PATH"] ?>"/>
                                </video>
                            </a>
                        <? endforeach; ?>
                    </div>
                </div>

                <div class="modal fade" id="gallery__popup" tabindex="-1" role="dialog" aria-labelledby="gallery__popup" aria-hidden="true">
                    <button class="close gallery__popup-close" type="button" data-dismiss="modal" aria-label="Закрыть">
                        <svg class="icon icon-close ">
                            <use xlink:href="#close"></use>
                        </svg>
                    </button>
                    <div class="modal-dialog gallery__popup-dialog" role="document">
                        <div class="modal-content gallery__popup-content">
                            <div class="modal-body gallery__popup-body">
                                <div class="gallery__popup-list mb-0"></div>
                                <div class="gallery__popup-thumbs"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <? endif; ?>
        <div class="col-lg-5">
            <form>
                <div class="product__header">
                    <h1 class="product__title"><?= $name ?></h1>
                    <div class="<?= $price["oldPrice"] ? "product__final-price" : "" ?>">
                        <span class="product__price" id="<?= $itemIds['PRICE_ID'] ?>">
                            <?= $price['PRINT_PRICE'] ?>
                        </span>
                        <?
                        if ($price["PRICE_DOLLAR"]): ?>
                            &middot; <span class="product__price">
                            <?= $price["PRICE_DOLLAR"] ?>
                        </span>
                        <? endif; ?>
                    </div>
                    <?if (!empty($price["oldPrice"])):?>
                    <div class="product__price-crossed">
                        <span class="product__price" id="<?= $itemIds['PRICE_ID'] ?>">
                            <?= $price["oldPriceFormat"] ?>
                        </span>
                        <?
                        if ($price["PRICE_DOLLAR"]): ?>
                            &middot; <span class="product__price">
                            <?= $price["oldPriceDollarFormat"] ?>
                        </span>
                        <? endif; ?>
                    </div>
                    <?endif;?>
                </div>
                <?
                if ($haveOffers && !empty($arResult['OFFERS_PROP'])): ?>
                    <div id="<?= $itemIds['TREE_ID'] ?>">
                        <?
                        foreach ($arResult['SKU_PROPS'] as $skuProperty): ?>
                            <? if (!isset($arResult['OFFERS_PROP'][$skuProperty['CODE']])):
                                continue;
                                ?>
                            <?endif;
                            $skuProps[] = array(
                                'ID' => $skuProperty['ID'],
                                'SHOW_MODE' => $skuProperty['SHOW_MODE'],
                                'VALUES' => $skuProperty['VALUES'],
                                'VALUES_COUNT' => $skuProperty['VALUES_COUNT']
                            );
                            ?>
                            <?
                            if ($skuProperty['CODE'] === "SIZE_REF"): ?>
                                <div class="dimension" data-entity="sku-line-block">
                                    <div class="dimension__group btn-group-toggle" data-toggle="buttons">
                                        <? foreach ($skuProperty['VALUES'] as $value): ?>
                                            <label class="btn dimension__btn dimension__btn_auto-width"
                                                   data-treevalue="<?= $skuProperty['ID'] ?>_<?= $value['ID'] ?>"
                                                   data-onevalue="<?= $value['ID'] ?>"
                                                   style="box-shadow: none"
                                            >
                                                <input id="dimension<?= $skuProperty['ID'] ?><?= $value['ID'] ?>"
                                                       type="radio"
                                                       name="dimension"
                                                       autocomplete="off"
                                                >
                                                <?= $value['NAME'] ?>
                                            </label>
                                        <? endforeach; ?>
                                    </div>
                                    <!-- Button modal-->
                                    <button class="btn dimension__table-btn" type="button" data-toggle="modal"
                                            data-target="#dimension__table-modal"><?= Loc::getMessage("SIZE_TABLE") ?></button>
                                    <!-- Modal-->
                                    <div class="modal fade" id="dimension__table-modal" tabindex="-1" role="dialog"
                                         aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><?= Loc::getMessage("SIZE_TABLE") ?></h5>
                                                    <button class="close" type="button" data-dismiss="modal"
                                                            aria-label="<?= Loc::getMessage("CLOSE_MODAL") ?>">
                                                        <svg class="icon icon-close ">
                                                            <use xlink:href="#close"></use>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div class="modal-body px-0">
                                                    <div class="d-lg-none">
                                                        <table class="table">
                                                            <thead>
                                                            <tr>
                                                                <td>XS</td>
                                                                <td>S</td>
                                                                <td>M</td>
                                                            </tr>
                                                            </thead>
                                                            <thead>
                                                            <tr>
                                                                <th colspan="4"><?= Loc::getMessage("CHEST_CIRCUMFERENCE") ?></th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr>
                                                                <td>84–88</td>
                                                                <td>89–92</td>
                                                                <td>93–96</td>
                                                            </tr>
                                                            </tbody>
                                                            <thead>
                                                            <tr>
                                                                <th colspan="4"><?= Loc::getMessage("HOIST_GIRTH") ?></th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr>
                                                                <td>60–64</td>
                                                                <td>65–69</td>
                                                                <td>70–75</td>
                                                            </tr>
                                                            </tbody>
                                                            <thead>
                                                            <tr>
                                                                <th colspan="4"><?= Loc::getMessage("HIP_GIRTH") ?></th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr>
                                                                <td>89–93</td>
                                                                <td>94–97</td>
                                                                <td>98–102</td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <? endif; ?>
                        <? endforeach; ?>

                        <? if (!empty($arResult["COLORS"])): ?>
                            <div class="color" data-entity="sku-line-block">
                                <? if (!empty($arResult["COLOR_NAME"])): ?>
                                    <div class="color__title">
                                        <?= Loc::getMessage("CURRENT_COLOR") ?>: <?= $arResult["COLOR_NAME"] ?>
                                        <span class="js-color__text"></span>
                                    </div>
                                <? endif; ?>
                                <div class="color__group btn-group-toggle">
                                    <? foreach ($arResult["COLORS"] as $item): ?>
                                        <? if ($item["ACTIVE"]): ?>
                                            <label class="btn color__btn js-color__btn active"
                                                   title="<?= $item["COLOR_NAME"] ?>"
                                                   style="box-shadow: none"
                                            >
                                            <span class="color__value"
                                                  style="background-image: url('<?= $item["COLOR"]['UF_FILE'] ?>');"
                                            ></span>
                                            </label>
                                        <? else: ?>
                                            <label class="btn color__btn js-color__btn"
                                                   title="<?= $item["COLOR_NAME"] ?>"
                                                   style="box-shadow: none"
                                            >
                                                <a href="<?= $item["DETAIL_PAGE_URL"] ?>">
                                        <span class="color__value"
                                              style="background-image: url('<?= $item["COLOR"]['UF_FILE'] ?>');"
                                        ></span>
                                                </a>
                                            </label>
                                        <? endif; ?>
                                    <? endforeach; ?>
                                </div>
                            </div>
                        <? endif; ?>
                    </div>
                <? endif; ?>
                <div id="<?= $itemIds['BASKET_ACTIONS_ID'] ?>"
                     style="display: <?= ($actualItem['CAN_BUY'] ? '' : 'none') ?>;">
                    <button class="btn btn-dark btn-block mb-4 js-btn__add-basket"
                            type="button"
                            id="<?= $itemIds['ADD_BASKET_LINK'] ?>"
                    >
                        <svg class="icon icon-basket product__basket-icon">
                            <use xlink:href="#basket"></use>
                        </svg>
                        <span class="js-text-content" data-added-text="<?= Loc::getMessage("ADDED_TO_BASKET") ?>">
                            <?= Loc::getMessage("ADD_TO_BASKET") ?>
                        </span>
                    </button>
                </div>
                <button class="btn btn-dark btn-block mb-3"
                        type="button"
                        id="<?= $itemIds['NOT_AVAILABLE_MESS'] ?>"
                        onclick="return false;"
                        style="display: <?= (!$actualItem['CAN_BUY'] ? '' : 'none') ?>"
                        disabled
                >
                    <svg class="icon icon-stop product__basket-icon">
                        <use xlink:href="#stop"></use>
                    </svg>

                    <?= Loc::getMessage("NOT_AVAILABLE") ?>
                </button>

                <div class="js-subscribe-buttons"
                     style="display:<?= !$actualItem["CAN_BUY"] ? "block" : "none" ?>;"
                >
                <?
                if (!$actualItem["CAN_BUY"]): ?>
                    <button class="btn btn-block mb-4 btn-outline-dark bx-catalog-subscribe-button js-open-subscribe"
                            data-toggle="modal" data-target="#subscribe-modal"
                            type="button"
                            style="<?= ($actualItem["CAN_BUY"] ? 'display: none;' : '') ?>">
                        <svg width="14" height="16" viewBox="0 0 14 16" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1.5791 12.624H8.46289C8.4082 13.5469 7.82031 14.1348 6.99316 14.1348C6.17285 14.1348 5.57812 13.5469 5.53027 12.624H4.46387C4.51855 13.9365 5.55078 15.0918 6.99316 15.0918C8.44238 15.0918 9.47461 13.9434 9.5293 12.624H12.4141C13.0566 12.624 13.4463 12.2891 13.4463 11.7969C13.4463 11.1133 12.749 10.498 12.1611 9.88965C11.71 9.41797 11.5869 8.44727 11.5322 7.66113C11.4844 4.96777 10.7871 3.22461 8.96875 2.56836C8.73633 1.67969 8.00488 0.96875 6.99316 0.96875C5.98828 0.96875 5.25 1.67969 5.02441 2.56836C3.20605 3.22461 2.50879 4.96777 2.46094 7.66113C2.40625 8.44727 2.2832 9.41797 1.83203 9.88965C1.2373 10.498 0.546875 11.1133 0.546875 11.7969C0.546875 12.2891 0.929688 12.624 1.5791 12.624ZM1.87305 11.5918V11.5098C1.99609 11.3047 2.40625 10.9082 2.76172 10.5049C3.25391 9.95801 3.48633 9.08301 3.54785 7.74316C3.60254 4.7627 4.49121 3.80566 5.66016 3.49121C5.83105 3.4502 5.92676 3.36133 5.93359 3.19043C5.9541 2.47266 6.36426 1.97363 6.99316 1.97363C7.62891 1.97363 8.03223 2.47266 8.05957 3.19043C8.06641 3.36133 8.15527 3.4502 8.32617 3.49121C9.50195 3.80566 10.3906 4.7627 10.4453 7.74316C10.5068 9.08301 10.7393 9.95801 11.2246 10.5049C11.5869 10.9082 11.9902 11.3047 12.1133 11.5098V11.5918H1.87305Z"
                                  fill="#212121"/>
                        </svg>
                        <span><?= Loc::getMessage("PREORDER") ?></span>
                    </button>
                <? endif; ?>
                </div>
                <button type="button"
                        class="modal-toggle"
                        style="display:none;"
                ></button>
                <div class="js-subscribe-modal modal fade" id="subscribe-modal" tabindex="-1" role="dialog"
                     aria-hidden="true">
                    <div class="js-subscribe-form modal-dialog modal-dialog-centered product-subscribe__dialog"
                         role="document"
                    >
                        <div class="modal-content">
                            <button class="close product-subscribe__close" type="button" data-dismiss="modal"
                                    aria-label="Закрыть">
                                <svg class="icon icon-close ">
                                    <use xlink:href="#close"></use>
                                </svg>
                            </button>
                            <div class="product-subscribe__header">
                                <h5 class="product-subscribe__title"><?= Loc::getMessage("TITLE_POPUP_SUBSCRIBED") ?></h5>
                            </div>
                            <div class="js-subscribe-form-body modal-body product-subscribe__body">
                                <div class="text-center px-lg-1 mb-3"><?= Loc::getMessage("DESC_POPUP_SUBSCRIBED") ?></div>
                                <form id="bx-catalog-subscribe-form">
                                    <input type="hidden" class="js-preorder-productId" name="productId"
                                           value="<?= $actualItem["ID"] ?>">
                                    <input type="hidden" name="siteId" value="<?= SITE_ID ?>">
                                    <div id="bx-catalog-subscribe-form-div" class="form-group">
                                        <div class="js-errors" style="color: red;"></div>
                                        <label for="subscribe-email" class="sr-only">E-mail</label>
                                        <input id="subscribe-email" class="form-control" type="text"
                                               name="email" placeholder="E-mail">
                                        <p></p>
                                        <label for="subscribe-tel"
                                               class="sr-only"><?= Loc::getMessage("PHONE_POPUP_SUBSCRIBED") ?></label>
                                        <input id="subscribe-tel" class="form-control" type="text"
                                               name="phone"
                                               placeholder="<?= Loc::getMessage("PHONE_POPUP_SUBSCRIBED") ?>">
                                    </div>
                                    <button type="submit" class="js-subscribe-button btn btn-dark btn-block">
                                        <?= Loc::getMessage("BTN_SEND_POPUP_SUBSCRIBED") ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="js-subscribe-suc modal-dialog modal-dialog-centered product-subscribe__dialog"
                         style="display: none"
                         role="document"
                    >
                        <div class="modal-content">
                            <button class="close product-subscribe__close" type="button" data-dismiss="modal"
                                    aria-label="Закрыть">
                                <svg class="icon icon-close ">
                                    <use xlink:href="#close"></use>
                                </svg>
                            </button>
                            <div class="product-subscribe__header">
                                <h5 class="product-subscribe__title"><?= Loc::getMessage("TITLE_POPUP_SUBSCRIBED") ?></h5>
                            </div>
                            <div class="modal-body product-subscribe__body">
                                <div class="js-text text-center px-lg-1 mb-3"><?= Loc::getMessage("SUCCESS_POPUP_SUBSCRIBED") ?></div>
                                <button class="btn btn-dark btn-block" data-dismiss="modal" aria-label="Закрыть"
                                        type="submit"><?= Loc::getMessage("CPST_SUBSCRIBE_BUTTON_CLOSE") ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <?
                if (!empty($arResult["DETAIL_TEXT"]) || !empty($arResult["PRODUCT_PROPERTIES"])): ?>
                    <div class="product__desc">
                        <? if ($arResult['DETAIL_TEXT_TYPE'] === 'html'): ?>
                            <?= $arResult['DETAIL_TEXT'] ?>
                        <? else: ?>
                            <p><?= $arResult['DETAIL_TEXT'] ?></p>
                        <? endif; ?>

                        <? if (!empty($arResult["PRODUCT_PROPERTIES"])): ?>
                            <ul class="list__disc">
                                <? foreach ($arResult["PRODUCT_PROPERTIES"] as $property): ?>
                                    <li><?= $property["NAME"] ?>: <?= $property["DISPLAY_VALUE"] ?></li>
                                <? endforeach; ?>
                            </ul>
                        <? endif; ?>

                        <? if (!empty($arResult["MEASUREMENTS"]) || !empty($arResult["MEASUREMENTS_ROWS"])): ?>
                            <!-- Button modal-->
                            <button class="btn measurement__btn" type="button" data-toggle="modal"
                                    data-target="#measurement__modal"><?= Loc::getMessage("MEASUREMENTS") ?></button>
                            <!-- Modal-->
                            <div class="modal fade" id="measurement__modal" tabindex="-1" role="dialog"
                                 aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered measurement__modal-dialog"
                                     role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><?= Loc::getMessage("MEASUREMENTS") ?></h5>
                                            <button class="close" type="button" data-dismiss="modal"
                                                    aria-label="Закрыть">
                                                <svg class="icon icon-close ">
                                                    <use xlink:href="#close"></use>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="modal-body measurement__modal-body">
                                            <? if (!empty($arResult["MEASUREMENTS"])): ?>
                                                <?= $arResult["MEASUREMENTS"] ?>
                                            <? else: ?>
                                                <div class="measurement__item">
                                                    <?
                                                    foreach ($arResult["MEASUREMENTS_ROWS"] as $row): ?>
                                                        <div><?= $row ?></div>
                                                    <? endforeach; ?>
                                                </div>
                                            <? endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <? endif; ?>
                    </div>
                <? endif; ?>
                <div class="product__question">
                    <div class="product__question-title mb-2"><?= Loc::getMessage("EXIST_QUESTIONS") ?></div>
                    <div class="d-flex">
                        <a class="btn btn-outline-secondary product__question-btn"
                           target="_blank"
                           href="https://wa.me/79153921144">
                            <svg class="icon icon-whatsapp ">
                                <use xlink:href="#whatsapp"></use>
                            </svg>
                            WhatsApp</a>
                        <a class="btn btn-outline-secondary product__question-btn"
                           target="_blank"
                           href="https://instagram.com/le4v4el?igshid=688ic3aif5yp">
                            <svg class="icon icon-instagram ">
                                <use xlink:href="#instagram"></use>
                            </svg>
                            Instagram</a>
                    </div>
                </div>
            </form>
        </div>
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
