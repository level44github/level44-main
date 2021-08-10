<?php

use Bitrix\Main\Context;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Fuser;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$request = Context::getCurrent()->getRequest();
$response = [];

try {
    if (!check_bitrix_sessid()) {
        throw new Exception("incorrect sessid");
    }

    if ($request->get("action") === "removeFromBasket") {
        if (!$request->isPost()) {
            throw new Exception("incorrect request method");
        }

        $basketItemId = (int)$request->get("basketItemId");

        if ($basketItemId <= 0) {
            throw new Exception("incorrect basketItemId");
        }

        $basket = Basket::loadItemsForFUser(Fuser::getId(), $request->get("siteId"));
        if ($basketItem = $basket->getItemById($basketItemId)) {
            $basketItem->delete();
            $basket->save();
        }
        $response["success"] = true;
    }
} catch (\Exception $e) {
    $response["error"] = $e->getMessage();
}

echo json_encode($response);