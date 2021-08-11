<?php

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Fuser;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

global $APPLICATION;
$APPLICATION->RestartBuffer();

$request = Context::getCurrent()->getRequest();
$result = [
    "availableBasket"     => false,
    "unavailableProducts" => [],
];

try {
    if (!Loader::includeModule("sale")) {
        throw new \Exception();
    }

    $basket = Basket::loadItemsForFUser(Fuser::getId(), $request->get("siteId"));

    $productIds = array_map(function ($basketItem) {
        /** @var BasketItem $basketItem */
        return $basketItem->getProductId();
    }, $basket->getBasketItems());

    $product = new \Level44\Product();
    $ecommerceData = $product->getEcommerceData($productIds);

    foreach ($basket->getBasketItems() as $basketItem) {
        /** @var BasketItem $basketItem */
        if ($basketItem->getQuantity() > $ecommerceData[$basketItem->getProductId()]["quantity"]) {
            $result["unavailableProducts"][] = $basketItem->getProductId();
        }
    }

    if (empty($result["unavailableProducts"])) {
        $result["availableBasket"] = true;
    }
} catch (\Exception $e) {
}

die(json_encode($result));