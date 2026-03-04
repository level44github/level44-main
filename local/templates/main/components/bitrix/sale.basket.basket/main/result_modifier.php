<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */

if (!isset($arParams['DISPLAY_MODE']) || !in_array($arParams['DISPLAY_MODE'], array('extended', 'compact'))) {
    $arParams['DISPLAY_MODE'] = 'extended';
}

$arParams['USE_DYNAMIC_SCROLL'] = isset($arParams['USE_DYNAMIC_SCROLL']) && $arParams['USE_DYNAMIC_SCROLL'] === 'N' ? 'N' : 'Y';
$arParams['SHOW_FILTER'] = isset($arParams['SHOW_FILTER']) && $arParams['SHOW_FILTER'] === 'N' ? 'N' : 'Y';

$arParams['PRICE_DISPLAY_MODE'] = isset($arParams['PRICE_DISPLAY_MODE']) && $arParams['PRICE_DISPLAY_MODE'] === 'N' ? 'N' : 'Y';

if (!isset($arParams['TOTAL_BLOCK_DISPLAY']) || !is_array($arParams['TOTAL_BLOCK_DISPLAY'])) {
    $arParams['TOTAL_BLOCK_DISPLAY'] = array('top');
}

if (empty($arParams['PRODUCT_BLOCKS_ORDER'])) {
    $arParams['PRODUCT_BLOCKS_ORDER'] = 'props,sku,columns';
}

if (is_string($arParams['PRODUCT_BLOCKS_ORDER'])) {
    $arParams['PRODUCT_BLOCKS_ORDER'] = explode(',', $arParams['PRODUCT_BLOCKS_ORDER']);
}

$arParams['USE_PRICE_ANIMATION'] = isset($arParams['USE_PRICE_ANIMATION']) && $arParams['USE_PRICE_ANIMATION'] === 'N' ? 'N' : 'Y';
$arParams['EMPTY_BASKET_HINT_PATH'] = isset($arParams['EMPTY_BASKET_HINT_PATH']) ? (string)$arParams['EMPTY_BASKET_HINT_PATH'] : '/';
$arParams['USE_ENHANCED_ECOMMERCE'] = isset($arParams['USE_ENHANCED_ECOMMERCE']) && $arParams['USE_ENHANCED_ECOMMERCE'] === 'Y' ? 'Y' : 'N';
$arParams['DATA_LAYER_NAME'] = isset($arParams['DATA_LAYER_NAME']) ? trim($arParams['DATA_LAYER_NAME']) : 'dataLayer';
$arParams['BRAND_PROPERTY'] = isset($arParams['BRAND_PROPERTY']) ? trim($arParams['BRAND_PROPERTY']) : '';

/** Скрытие подарочных товаров (по ID и цене 0) в корзине — отображаются только в checkout */
if (!empty($arResult['GRID']['ROWS']) && is_array($arResult['GRID']['ROWS']) && class_exists(\Level44\Event\GiftOver40kHandlers::class)) {
    $giftIds = \Level44\Event\GiftOver40kHandlers::GIFT_PRODUCT_IDS;
    foreach ($arResult['GRID']['ROWS'] as $id => $row) {
        $productId = (int)($row['PRODUCT_ID'] ?? $row['ID'] ?? 0);
        $price = (float)($row['PRICE'] ?? 0);
        if (in_array($productId, $giftIds, true) && $price == 0) {
            unset($arResult['GRID']['ROWS'][$id]);
        }
    }
}
if (!empty($arResult['BASKET_ITEM_RENDER_DATA']) && is_array($arResult['BASKET_ITEM_RENDER_DATA']) && class_exists(\Level44\Event\GiftOver40kHandlers::class)) {
    $giftIds = \Level44\Event\GiftOver40kHandlers::GIFT_PRODUCT_IDS;
    foreach ($arResult['BASKET_ITEM_RENDER_DATA'] as $id => $row) {
        $productId = (int)($row['PRODUCT_ID'] ?? $row['ID'] ?? 0);
        $price = (float)($row['PRICE'] ?? 0);
        if (in_array($productId, $giftIds, true) && $price == 0) {
            unset($arResult['BASKET_ITEM_RENDER_DATA'][$id]);
        }
    }
}
