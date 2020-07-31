<?
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use \Level44\PreOrder;

require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/include/prolog_before.php');
global $USER;

$result = [
    "success" => null,
];

try {
    $request = Main\Context::getCurrent()->getRequest();

    if (!$request->isPost() || !check_bitrix_sessid()) {
        throw new \Exception();
    }

    if (empty($request->get("productId"))
        || empty($request->get("siteId"))) {
        if ($request->get("type") !== "adminConfirm") {
            throw new \Exception();
        }
    }

    $preOrder = new PreOrder($request->get("productId"), $request->get("siteId"));

    if ($request->get("type") === "checkSubscribed") {
        $result["initData"] = $preOrder->getInitData();
    } elseif ($request->get("type") === "adminConfirm") {
        if (empty($USER) || !$USER->IsAdmin() || empty($request->get("orderId"))) {
            throw new \Exception();
        }

        $order = \Bitrix\Sale\Order::load($request->get("orderId"));
        $result["success"] = PreOrder::sendOrderConfirm($order);
    } elseif ($request->get("type") === "subscribed") {

        $fields = [
            "email" => $request->get("email"),
            "phone" => $request->get("phone"),
        ];

        $preOrder->setFields($fields);
        $preOrder->subscribed();
        $result["success"] = $preOrder->isSuccess();
        $result["errorMes"] = $preOrder->getError();
        $result["initData"] = $preOrder->getInitData();
    }

    if ($result["success"] === null) {
        $result["success"] = true;
    }
} catch (Exception $e) {
}

$result["success"] = (bool)$result["success"];
echo Json::encode($result);
die();