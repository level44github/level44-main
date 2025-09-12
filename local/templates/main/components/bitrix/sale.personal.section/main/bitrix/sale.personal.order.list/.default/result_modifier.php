<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arResult
 * @var array $APPLICATION
 */

use Level44\Base;

$offersId = [];

foreach ($arResult['ORDERS'] as $order) {
    if (is_array($order['BASKET_ITEMS'])) {
        $orderProductsId = array_map(fn($item) => (int)$item['PRODUCT_ID'], $order['BASKET_ITEMS']);
        $offersId = array_merge($offersId, $orderProductsId);
    }
}

$productList = CCatalogSKU::getProductList($offersId, Base::OFFERS_IBLOCK_ID);

$productIds = [];
foreach ($productList as $offerId => $product) {
    $productIds[$offerId] = $product["ID"];
}

$productIdsFilter = array_values(array_filter($productIds));

$products = [];
if (!empty($productIdsFilter)) {
    $rsProductsData = CIBlockElement::GetList(
        [],
        [
            "ID"        => $productIdsFilter,
            "IBLOCK_ID" => Base::CATALOG_IBLOCK_ID
        ],
        false,
        false,
        [
            "ID",
            "IBLOCK_ID",
            "PREVIEW_PICTURE",
            "DETAIL_PICTURE",
            "PROPERTY_MORE_PHOTO",
        ]
    );

    while ($product = $rsProductsData->fetch()) {
        $products[$product['ID']] = $product;
    }
}

foreach ($arResult['ORDERS'] as &$order) {
    foreach ($order['BASKET_ITEMS'] as &$orderItem) {
        $productData = $products[$productIds[$orderItem['PRODUCT_ID']]];
        if (!empty($productData)) {
            $pictures = \CIBlockPriceTools::getDoublePicturesForItem($productData, '');

            $orderItem['PICTURE'] = $pictures['PICT']['SRC'];
        }
    }
    unset($orderItem);
}
unset($order);