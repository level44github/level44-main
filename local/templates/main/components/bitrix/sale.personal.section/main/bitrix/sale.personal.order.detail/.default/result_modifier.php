<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Sale\EntityPropertyValue;
use Bitrix\Sale\Order;
use Level44\Base;
use Level44\Product;

$arResult['PROPERTIES'] = [];
foreach ($arResult['ORDER_PROPS'] as $orderProp) {
    $arResult['PROPERTIES'][$orderProp['CODE']] = $orderProp;
}

/** @var EntityPropertyValue|null $addressSdek */
[$addressSdek] = Order::load($arResult['ID'])->getPropertyCollection()->getItemsByOrderPropertyCode('ADDRESS_SDEK');

if ($addressSdek) {
    $arResult['ADDRESS_SDEK'] = $addressSdek->getValue();
}

$arResult['FIO'] = trim(join(' ', [
    $arResult['PROPERTIES']['LAST_NAME']['VALUE'],
    $arResult['PROPERTIES']['FIRST_NAME']['VALUE'],
    $arResult['PROPERTIES']['SECOND_NAME']['VALUE'],
]));


$basketCount = 0;

$basketProductIds = array_map(fn($basketItem) => (int)$basketItem['PRODUCT_ID'], $arResult['BASKET']);

$productList = CCatalogSKU::getProductList($basketProductIds);

$productsData = [];
foreach ($productList as $offerId => $product) {
    $productsData[$offerId] = $product["ID"];
}

$obProduct = new Product();
$ecommerceData = $obProduct->getEcommerceData(array_values($productsData));

array_walk($arResult['BASKET'], function (&$basketItem) use (&$basketCount, $ecommerceData, $productsData) {
    $basketCount = $basketCount + (int)$basketItem['QUANTITY'];
    $basketItem["PRICE_DOLLAR"] = Base::getDollarPrice($basketItem["PRICE"], notFormat: true);
    $basketItem["PRICE_DOLLAR_FORMAT"] = Base::formatDollar($basketItem["PRICE_DOLLAR"]);
    $prices = $ecommerceData[$productsData[$basketItem["PRODUCT_ID"]]]["prices"];
    if (is_array($prices)) {
        $basketItem = array_merge($basketItem, $prices);
    }

    $picturesData = [
        'PREVIEW_PICTURE' => $basketItem['PREVIEW_PICTURE'],
        'DETAIL_PICTURE'  => $basketItem['DETAIL_PICTURE'],
    ];

    $pictures = \CIBlockPriceTools::getDoublePicturesForItem($picturesData, '');
    $basketItem['PICTURE'] = $pictures['PICT']['SRC'];
});

$arResult['BASKET_COUNT'] = $basketCount;