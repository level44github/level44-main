<?php

namespace Podeli\Bnpl;

use \Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use \Bitrix\Main\Error;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Sale\Order;
use \Bitrix\Sale\Result;


class ClientWrapper
{
    public function __constructor()
    {
        Loc::loadMessages(__FILE__);
    }

    public function getTransactionList($filter, $sort = [])
    {
        $connection = Application::getConnection();
        $sqlHelper  = $connection->getSqlHelper();
        $byOrder = [];
        if (!empty($sort)) {
            foreach ($sort as $by => $order) {
                $order = mb_strtolower($order);
                if ($order != 'asc') {
                    $order = 'desc';
                }
                if ($by == 'ORDER_NUMBER') {
                    $by = "LENGTH(ORDER_NUMBER) $order, ORDER_NUMBER";
                }
                $byOrder[] = "$by $order";
            }
            $byOrder = implode(',', $byOrder);
        } else {
            $byOrder = '`CREATED` desc';
        }
        $where = array();
        foreach ($filter as $key => $item) {
            $where[] = "$key= '" . $sqlHelper->forSql($item) . "'";
        }
        if ($where) {
            $where = 'WHERE ' . implode(" AND ", $where);
        } else {
            $where = '';
        }
        $sql = "SELECT *
                FROM podeli_bnpl_request
                $where order by $byOrder";
        $dbRes = $connection->query($sql);
        return $dbRes;
    }

    protected function isOrderCompletelyRefunded($orderInfo) {
        $ordered = array_reduce($orderInfo['order']['items'], function  ($carry, $item) {
            return $carry + intval($item['quantity']);
        });
        $refunded = 0;
        $refunds = $orderInfo['refund'];
        foreach ($refunds as $refund) {
            foreach ($refund['refundedItems'] as $redundItem) {
                $refunded += $redundItem['refundedQuantity'];
            }
        }
        return $ordered == $refunded;
    }

    public function getOrderItems($id)
    {
        $connection = Application::getConnection();
        $sqlHelper  = $connection->getSqlHelper();
        $row = $connection
            ->query("SELECT * 
                     FROM podeli_bnpl_request 
                     WHERE ID = '" . $sqlHelper->forSql($id) . "'")
            ->fetch();
        if (!$row) {
            return false;
        }
        return \Bitrix\Main\Web\Json::decode($row['ITEMS']);
    }

    protected function removeOrderItemsOnRefund($order, $paymentDataRefundItems, $result)
    {
        $basket = $order->getBasket();
        try {
            foreach ($paymentDataRefundItems as $item) {
                $orderBasketItem = $basket->getItemById($item['id']);
                if (!$orderBasketItem) continue;
                $newQuantity = $orderBasketItem->getQuantity() - $item['refundedQuantity'];
                if ($newQuantity > 0) {
                    $subResult = $orderBasketItem->setField('QUANTITY', $newQuantity);
                } else {
                    $subResult = $orderBasketItem->delete();
                }
                if (!$subResult->isSuccess()) {
                    $result->addError(new Error(Loc::getMessage('PODELI.PAYMENT_ERROR_CANT_REFUND_ORDER')));
                }
                $orderBasketItem->save();
            }
            $basket->save();
        } catch (\Exception $e) {
            $result->addError(new Error($e->getMessage()));
        }
    }

    public function processAction($post)
    {
        $result = new Result();
        if (!isset($post['action'])) {
            return $result->addError(new Error(Loc::getMessage('PODELI.PAYMENT_ERROR_EMPTY_ACTION')));
        }
        $connection = Application::getConnection();
        $sqlHelper = $connection->getSqlHelper();
        $orderData = $connection
            ->query("SELECT * 
                     FROM podeli_bnpl_request 
                     WHERE ID = '" . $sqlHelper->forSql($post['id']) . "'")
            ->fetch();
        if (!$orderData) {
            return $result->addError(new Error(Loc::getMessage('PODELI.PAYMENT_ERROR_ORDER_NOT_FOUND')));
        }
        $order = Order::load($orderData['ORDER_ID']);
        if (!$order) {
            return $result->addError(new Error(Loc::getMessage('PODELI.PAYMENT_ERROR_ORDER_LOAD')));
        }
        $payment = $order
            ->getPaymentCollection()
            ->getItemById($orderData['PAYMENT_ID']);

        if (!$payment) {
            return $result->addError(new Error(Loc::getMessage('PODELI.PAYMENT_ERROR_PAYMENT_NOT_FOUND')));
        }
        $params = $payment
            ->getPaySystem()
            ->getParamsBusValue($payment);
        $params['ORDER_NUMBER'] = $orderData['ORDER_NUMBER'];

        if ($post['action'] == 'refund') {
            $result = $this->refundOrder($params, $post);
            if ($result->isSuccess()) {
                $paymentData = $result->getData();
                self::saveRefundId($paymentData['order']['id'], $paymentData['refund']['id']);
                self::updateTransactionStatus($paymentData['order']['id'], 'REFUNDED');
                self::updateItems($paymentData, $orderData);
                $amount = $orderData['AMOUNT'] - (float)$paymentData['refund']['totalRefundedAmount'];
                self::updateAmount($paymentData['order']['id'], $amount);
                $removeRefundedItems = Option::get('podeli.bnpl', 'remove_refunded_items_from_order');
                if ($removeRefundedItems) {
                    $this->removeOrderItemsOnRefund($order, $paymentData['refund']['items'], $result);
                }
                $psData = [
                    "PS_STATUS_CODE" => 'refund',
                    "PS_STATUS_DESCRIPTION" => 'Refund id:' . $paymentData['refund']['id'],
                    "PS_RESPONSE_DATE" => new \Bitrix\Main\Type\DateTime(),
                ];
                if ($paymentData['refund']['totalRefundedAmount']) {
                    $psData["PAID"] = $amount != 0 ? 'Y' : 'N';
                    $psData["PS_STATUS"] = 'Y';
                }
                $payment->setFields($psData);
                $order->save();
            } else {
                return $result->addError(new Error(Loc::getMessage('PODELI.PAYMENT_ERROR_CANT_REFUND_ORDER')));
            }
        } else if ($post['action'] == 'commit') {
            $result = $this->commitOrder($params, $orderData);
            if ($result->isSuccess()) {
                $paymentData = $result->getData();
                self::updateTransactionStatus($paymentData['order']['id'], 'COMMITED');
                self::updateTransactionPaymentSchedule($paymentData['order']['id'], $paymentData['paymentSchedule']);
                $psData = [
                    "PS_STATUS_CODE" => 'commit',
                    "PS_SUM" => $paymentData['amount'],
                    "PS_RESPONSE_DATE" => new \Bitrix\Main\Type\DateTime(),
                ];
                if ($paymentData['amount']) {
                    $psData["SUM"] = $paymentData['amount'];
                    $psData["PAID"] = 'Y';
                    $psData["PS_STATUS"] = 'Y';
                }
                $payment->setFields($psData);
                $order->save();
            } else {
                return $result->addError(new Error(Loc::getMessage('PODELI.PAYMENT_ERROR_CANT_COMMIT_ORDER')));
            }
        } else if ($post['action'] == 'cancel') {
            $result = $this->cancelOrder($params);
            if ($result->isSuccess()) {
                $paymentData = $result->getData();
                self::updateTransactionStatus($paymentData['order']['id'], 'CANCELLED');
                self::updateTransactionPaymentSchedule($paymentData['order']['id'], $paymentData['paymentSchedule']);
                if (mb_strtolower($paymentData['order']['status']) === 'created' && $this->isPaymentProcessed($payment)) {
                    //to lazy to invert this if
                } else {
                    $payment->setPaid('N');
                    $psData = [
                        "PS_STATUS_CODE" => 'cancel',
                        "PS_SUM" => $paymentData['amount'],
                        "PS_RESPONSE_DATE" => new \Bitrix\Main\Type\DateTime(),
                    ];
                    $payment->setFields($psData);
                    $order->setField('CANCELED', 'Y');
                    $order->save();
                }
            } else {
                return $result->addError(new Error(Loc::getMessage('PODELI.PAYMENT_ERROR_CANT_CANCEL_ORDER')));
            }
        } else if ($post['action'] == 'update') {
            $result = $this->updateOrder($params);
            if ($result->isSuccess()) {
                $paymentData = $result->getData();
                self::updateTransactionStatus($paymentData['order']['id'], $paymentData['order']['status']);
                self::updateTransactionPaymentSchedule($paymentData['order']['id'], $paymentData['paymentSchedule']);
                if (!$this->isOrderCompletelyRefunded($paymentData)) {
                    if (in_array(mb_strtolower($paymentData['order']['status']), ['completed'])) {
                        $payment->setPaid('Y');
                        $psData = array(
                            "PS_STATUS_CODE" => $paymentData['order']['status'],
                            "PS_SUM" => $paymentData['order']['amount'],
                            "PS_RESPONSE_DATE" => new \Bitrix\Main\Type\DateTime(),
                        );
                        $payment->setFields($psData);
                        $payedStatusBinding = Option::get('podeli.bnpl', 'payed_status_binding');
                        if (!empty($payedStatusBinding) && $payedStatusBinding != 0) {
                            $order->setField("STATUS_ID", $payedStatusBinding);
                        }       
                        $order->save();
                    }
                }
            } else {
                return $result->addError(new Error(Loc::getMessage('PODELI.PAYMENT_ERROR_CANT_UPDATE_ORDER')));
            }
        } else {
            return $result->addError(new Error(Loc::getMessage('PODELI.PAYMENT_ERROR_WRONG_ACTION', ['ACTION' => $post['action']])));
        }
        return $result;
    }

    protected function isPaymentProcessed(Payment $payment)
    {
        $connection = Application::getConnection();
        $sqlHelper = $connection->getSqlHelper();
        $id = $payment->getId();
        $rows = $connection
            ->query("
						SELECT * 
						FROM podeli_bnpl_request 
						WHERE PAYMENT_ID = '" . $sqlHelper->forSql($id) . "'")
            ->fetchAll();
        foreach ($rows as $r) {
            $paymentProcessedStatuses = [
                'wait_for_commit',
                'committed',
                'approved',
                'completed',
                'refunded'
            ];
            if (in_array(mb_strtolower($r['STATUS']), $paymentProcessedStatuses)) {
                return true;
            }
        }
        return false;
    }
    protected function updateOrder($params)
    {
        $result = new Result();
        $debug = Option::get('podeli.bnpl', 'debug');
        $useCurlHandler = Option::get('podeli.bnpl', 'use_curl_handler');
        $api = new Client(
            $params['SHOP_NAME'],
            $params['SHOP_PASSWORD'],
            $params['CERT_PATH'],
            $params['KEY_PATH'],
            $params['CERT_INPUT'],
            $params['KEY_INPUT'],
            null,
            $debug,
            $useCurlHandler
        );
        try {
            $response = $api->info($params['ORDER_NUMBER']);
            $result->setData($response);
        } catch (\Exception $e) {
            $result->addError(new Error($e->getMessage()));
        }
        return $result;
    }

    protected function cancelOrder($params)
    {
        $result = new Result();
        $debug = Option::get('podeli.bnpl', 'debug');
        $useCurlHandler = Option::get('podeli.bnpl', 'use_curl_handler');
        $api = new Client(
            $params['SHOP_NAME'],
            $params['SHOP_PASSWORD'],
            $params['CERT_PATH'],
            $params['KEY_PATH'],
            $params['CERT_INPUT'],
            $params['KEY_INPUT'],
            null,
            $debug,
            $useCurlHandler
        );
        $data = self::prepareCancelData();
        try {
            $data = \Bitrix\Main\Web\Json::encode($data, JSON_UNESCAPED_UNICODE);
            $response = $api->cancel($params['ORDER_NUMBER'], $data);
            $result->setData($response);
        } catch (\Exception $e) {
            $result->addError(new Error($e->getMessage()));
        }
        return $result;
    }

    private static function prepareCancelData()
    {
        return [
            "cancellationInitiator" => "shop"
        ];
    }

    protected function commitOrder($params, $orderData)
    {
        $result = new Result();
        $debug = Option::get('podeli.bnpl', 'debug');
        $useCurlHandler = Option::get('podeli.bnpl', 'use_curl_handler');
        $api = new Client(
            $params['SHOP_NAME'],
            $params['SHOP_PASSWORD'],
            $params['CERT_PATH'],
            $params['KEY_PATH'],
            $params['CERT_INPUT'],
            $params['KEY_INPUT'],
            null,
            $debug,
            $useCurlHandler
        );
        $data = self::prepareCommitData($orderData);
        try {
            $data = \Bitrix\Main\Web\Json::encode($data, JSON_UNESCAPED_UNICODE);
            $response = $api->commit($params['ORDER_NUMBER'], $data);
            $result->setData($response);
        } catch (\Exception $e) {
            $result->addError(new Error($e->getMessage()));
        }
        return $result;
    }

    private static function prepareCommitData($orderData)
    {
        return [
            'order' => [
                'amount' => $orderData['AMOUNT'],
                'prepaidAmount' => $orderData['PREPAID_AMOUNT'],
            ]
        ];
    }

    protected function refundOrder($params, $post)
    {
        $result = new Result();
        $debug = Option::get('podeli.bnpl', 'debug');
        $useCurlHandler = Option::get('podeli.bnpl', 'use_curl_handler');
        $api = new Client(
            $params['SHOP_NAME'],
            $params['SHOP_PASSWORD'],
            $params['CERT_PATH'],
            $params['KEY_PATH'],
            $params['CERT_INPUT'],
            $params['KEY_INPUT'],
            null,
            $debug,
            $useCurlHandler
        );
        $data = self::prepareRefundData($post);
        try {
            $data = \Bitrix\Main\Web\Json::encode($data, JSON_UNESCAPED_UNICODE);
            $response = $api->refund($params['ORDER_NUMBER'], $data);
            $result->setData($response);
        } catch (\Exception $e) {
            $result->addError(new Error($e->getMessage()));
        }
        return $result;
    }

    protected function prepareRefundData($post)
    {
        $refundItems = [];
        foreach ($post['position'] as $position) {
            $item = [
                "id" => $post["item_id"][$position],
                "refundedQuantity" => $post['quantity'][$position],
            ];
            $refundItems[] = $item;
        }
        return [
            'order' => [
                'id' => $post['id'],
                'refund' => [
                    'id' => ClientUtils::generateCorrelationId(),
                    'initiator' => 'shop',
                    'items' => $refundItems
                ]
            ]
        ];
    }

    public function calcPrepaidAmount($amount, $items)
    {
        $itemsSum = array_reduce($items, function ($carry, $item) {
            $carry += $item['amount'] * $item['quantity'];
            return $carry;
        });
        $diff = $itemsSum - $amount;
        return number_format($diff, 2, '.', '');
    }

    private static function updateTransactionPaymentSchedule($id, $paymentSchedule)
    {
        $paymentSchedule =
            \Bitrix\Main\Web\Json::encode(
                isset($paymentSchedule) ?
                    $paymentSchedule :
                    null
            );
        $connection = Application::getConnection();
        $sqlHelper = $connection->getSqlHelper();
        $connection->query("UPDATE podeli_bnpl_request 
                            SET PAYMENT_SCHEDULE = '" . $sqlHelper->forSql($paymentSchedule) . "' 
                            WHERE ORDER_NUMBER = '" . $sqlHelper->forSql($id) . "'");
    }

    protected function updateTransactionStatus($id, $status)
    {
        $connection = Application::getConnection();
        $sqlHelper = $connection->getSqlHelper();
        $connection->query("UPDATE podeli_bnpl_request 
                            SET STATUS = '" . $sqlHelper->forSql($status) . "' 
                            WHERE ORDER_NUMBER = '" . $sqlHelper->forSql($id) . "'");
    }

    protected function saveRefundId($id, $refundId)
    {
        $connection = Application::getConnection();
        $sqlHelper = $connection->getSqlHelper();
        $connection->query("UPDATE podeli_bnpl_request 
                            SET REFUND_ID = '" . $sqlHelper->forSql($refundId) . "' 
                            WHERE ORDER_NUMBER = '" . $sqlHelper->forSql($id) . "'");
    }

    private static function updateItems($paymentData, $orderData)
    {
        $id = $paymentData['order']['id'];
        $connection = Application::getConnection();
        $sqlHelper = $connection->getSqlHelper();
        $orderItems = \Bitrix\Main\Web\Json::decode($orderData['ITEMS'], JSON_UNESCAPED_UNICODE);
        $items = [];
        $refund = isset($orderItems['refund']) && count($orderItems['refund']) ? $orderItems['refund'] : [];
        foreach ($orderItems['items'] as $item) {
            foreach ($paymentData['refund']['items'] as $refundedItem) {
                if ($refundedItem['id'] == $item['id']) {
                    $item['quantity'] -= $refundedItem['refundedQuantity'];
                    if (key_exists($item['id'], $refund)) {
                        $refund[$item['id']]['amount'] += $refundedItem['refundedAmount'];
                        $refund[$item['id']]['quantity'] += $refundedItem['refundedQuantity'];
                    } else {
                        $refund[$item['id']] = [
                            'id' => $item['id'],
                            'name' => $item['name'],
                            'quantity' => $refundedItem['refundedQuantity'],
                            'article' => $item['article'],
                            'amount' => $refundedItem['refundedAmount']
                        ];
                    }
                }
            }
            if ($item['quantity'] > 0) {
                $items[] = $item;
            }
        }
        $items = \Bitrix\Main\Web\Json::encode(['items' => $items, 'refund' => $refund], JSON_UNESCAPED_UNICODE);
        $connection->query("UPDATE podeli_bnpl_request 
                            SET ITEMS = '" . $sqlHelper->forSql($items) . "' 
                            WHERE ORDER_NUMBER = '" . $sqlHelper->forSql($id) . "'");
    }

    private static function updateAmount($id, $amount)
    {
        $connection = Application::getConnection();
        $sqlHelper = $connection->getSqlHelper();
        $connection->query("UPDATE podeli_bnpl_request 
                            SET AMOUNT = '" . $sqlHelper->forSql($amount) . "' 
                            WHERE ORDER_NUMBER = '" . $sqlHelper->forSql($id) . "'");
    }
}
