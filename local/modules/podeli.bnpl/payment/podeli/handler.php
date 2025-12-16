<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\PriceMaths;
use CIBlockElement;
use Podeli\Bnpl\Client;
use Podeli\Bnpl\ClientUtils;
use Podeli\Bnpl\ClientWrapper;

\CModule::IncludeModule("podeli.bnpl");


class PodeliHandler extends PaySystem\ServiceHandler
{
  private const CORRELATION_HEADER_KEY = 'x-correlation-id';

  public function initiatePay(Payment $payment, Request $request = null)
  {
    $result = new PaySystem\ServiceResult();
    $paymentShouldPay = number_format(PriceMaths::roundPrecision($payment->getSum()), 2, '.', '');
    $order = $payment->getCollection()->getOrder();
    $orderItems = $this->getOrderItems($payment);
    $prepaidAmount = (new ClientWrapper())->calcPrepaidAmount($paymentShouldPay, $orderItems);
    $autoCommit = Option::get('podeli.bnpl', 'auto_commit');
    $orderNumber = ClientUtils::prepareOrderId($this->getBusinessValue($payment, 'ORDER_NUMBER'));

    $multiOrder = Option::get('podeli.bnpl', 'multi_order_request');
    if ($multiOrder) {
      $orderNumber = $this->fixPodeliPaymentIdentifier($payment, $order, $orderNumber);
    }

    $dbRes = \Bitrix\Sale\ShipmentCollection::getList([
      'select' => ['*'],
      'filter' => [
        '=ORDER_ID' => $order->getId()
      ]
    ]);
    $deliveryItem = null;
    while ($item = $dbRes->fetch()) {
      $deliveryItem = $item;
      break;
    }

    $deliveryMethod = null;
    if ($deliveryItem && array_key_exists('DELIVERY_NAME', $deliveryItem)) {
      $deliveryMethod = $deliveryItem['DELIVERY_NAME'];
    }

    $data = [
      'order' => [
        'id' => $orderNumber,
        'amount' => $paymentShouldPay,
        'prepaidAmount' => $prepaidAmount,
        'items' => $orderItems,
        'isTwoStagePayment' => $autoCommit == '0',
        'phoneRecipient' => $this->getPhone($order),
      ],
      'clientInfo' => [
        'firstName' => $this->getFirstName($order),
        'lastName' => $this->getLastName($order),
        'phone' => $this->getPhone($order),
        'email' => $this->getEmail($order),
      ],
      'notificationUrl' => $this->getNotifyUrl($payment),
      'failUrl' => $this->getFailUrl($payment),
      'successUrl' => $this->getSuccessUrl($payment),
    ];
    if (!empty($deliveryMethod)) {
      $data['order']['comment'] = 'deliveryMethod: ' . $deliveryMethod;
    }
    if ($this->isPaymentProcessed($payment)) {
      $result->addError(new Error(Loc::getMessage('PODELI.PAYMENT_ALREADY_PAYED')));
      Client::logToFile(Loc::getMessage('PODELI.PAYMENT_ALREADY_PAYED'));
      return $result;
    }
    $createResult = $this->createPayment($payment, $data);
    if (!$createResult->isSuccess()) {
      $errors = $createResult->getErrors();
      $result->addErrors($errors);
      Client::logToFile($errors);
      return $result;
    }
    $paymentData = $createResult->getData();
    $this->saveTransaction($data, $paymentData, $payment);
    if (method_exists($result, "setPaymentUrl")) {
      $result->setPaymentUrl($paymentData['redirectUrl']);
    }
    if ($this->needRedirect($payment)) {
      LocalRedirect($paymentData['redirectUrl'], true);
    }
    $this->setExtraParams($paymentData);
    $showTemplateResult = $this->showTemplate($payment, "template");
    if ($showTemplateResult->isSuccess()) {
      $result->setTemplate($showTemplateResult->getTemplate());
    } else {
      $result->addErrors($showTemplateResult->getErrors());
      Client::logToFile($showTemplateResult->getErrors());
    }
    return $result;
  }

protected function isOrderCompletelyRefunded($orderInfo) {
  $ordered = array_reduce($orderInfo['order']['items'], function ($carry, $item) {
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

  protected function needRedirect($payment)
  {
    if ($this->getBusinessValue($payment, 'AUTO_REDIRECT') != 'Y') {
      return false;
    }
    if (defined('BX_CRONTAB') && BX_CRONTAB === true) {
      return false;
    }
    $url  = $this->getCurrentUrl();
    $path = parse_url($url, PHP_URL_PATH);
    if ($path == false) {
      return false;
    }
    $exclideList = Option::get('podeli.bnpl', 'redirect_exclude_list');
    if (empty($exclideList)) {
      return true;
    }
    $exclideList = explode("\n", $exclideList);
    foreach ($exclideList as $url) {
      if (strpos($path, trim($url)) !== false) {
        return false;
      }
    }
    return true;
  }

  protected function fixPodeliPaymentIdentifier(Payment $payment, Order $order, $orderNumber)
  {
    if (!$this->isPaymentCreated($payment) || $order->isPaid()) {
      return $orderNumber . "_0";
    }
    $connection = Application::getConnection();
    $sqlHelper = $connection->getSqlHelper();
    $id = $payment->getId();
    $row = $connection
      ->query("
                SELECT ORDER_NUMBER
                FROM podeli_bnpl_request
                WHERE PAYMENT_ID = '" . $sqlHelper->forSql($id) . "'
                ORDER by ORDER_NUMBER DESC 
                LIMIT 1
            ")->fetch();
    $parts = explode('_', $row['ORDER_NUMBER']);
    $suffix = (int)(end($parts));
    $orderNumber .= "_" . (++$suffix);

    return $orderNumber;
  }

  protected function isPaymentCreated(Payment $payment)
  {
    $connection = Application::getConnection();
    $sqlHelper = $connection->getSqlHelper();
    $id = $payment->getId();
    $row = $connection
      ->query("SELECT id 
               FROM podeli_bnpl_request
               WHERE PAYMENT_ID = '" . $sqlHelper->forSql($id) . "'")->fetch();
    return !empty($row);
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
        'approved',
        'committed',
        'completed'
      ];
      if (in_array(mb_strtolower($r['STATUS']), $paymentProcessedStatuses)) {
        return true;
      }
    }
    return false;
  }

  public function getCurrencyList()
  {
    return ['RUB'];
  }

  public static function isMyResponse(Request $request, $paySystemId)
  {
    return $request->get('BX_PAYSYSTEM_CODE') === $paySystemId;
  }

  public static function isMyResponseExtended(Request $request, $paySystemId)
  {
    return $request->get('BX_PAYSYSTEM_CODE') === $paySystemId;
  }

  public function getPaymentIdFromRequest(Request $request)
  {
    return $request->get('BX_PAYMENT_ID');
  }

  protected function checkRightCorrelationAndReturnOrderNumber($payment)
  {
    $headers = getallheaders();
    $headerCorrelation = null;
    foreach ($headers as $key => $value) {
      if (strtolower($key) == self::CORRELATION_HEADER_KEY) {
        $headerCorrelation = trim($value);
      }
    }
    $connection = Main\Application::getConnection();
    $sqlHelper = $connection->getSqlHelper();
    $rows = $connection
      ->query("SELECT ORDER_NUMBER
             FROM podeli_bnpl_request 
             WHERE PAYMENT_ID = " . $sqlHelper->forSql($payment->getId()) . " AND 
             CORRELATION = '" . $sqlHelper->forSql($headerCorrelation) . "'")
      ->fetchAll();
    return count($rows) === 1 ? $rows[0]['ORDER_NUMBER'] : false;
  }

  public function processRequest(Payment $payment, Request $request)
  {
    $result = new PaySystem\ServiceResult();
    $checkingCorrelationResult = $this->checkRightCorrelationAndReturnOrderNumber($payment);
    if (!$checkingCorrelationResult) {
      $result->addError(new Error("Wrong correlation"));
      Client::logToFile("Wrong correlation");
      return $result;
    }
    $orderNumber = ClientUtils::prepareOrderId($checkingCorrelationResult);
    $orderInfoResult = $this->getOrderInfo($payment, $orderNumber);
    if (!$orderInfoResult->isSuccess()) {
      $result->addErrors($orderInfoResult->getErrors());
      Client::logToFile($orderInfoResult->getErrors());
      return $result;
    }
    $orderInfo = $orderInfoResult->getData();
    $this->updateTransaction($orderInfo, $payment);
    $order = $payment->getCollection()->getOrder();
    if (
      $this->getBusinessValue($payment, 'AUTO_CANCEL') === 'Y'
      && (in_array(mb_strtolower($orderInfo['order']['status']), ['rejected', 'cancelled']))
      && !$order->isPaid()
    ) {
      $order->setField("CANCELED", "Y");
      $cancelStatusBinding = Option::get('podeli.bnpl', 'cancelled_status_binding');
      if (!empty($cancelStatusBinding) && $cancelStatusBinding != 0) {
        $order->setField("STATUS_ID", $cancelStatusBinding);
      }
      $order->save();
      return $result;
    }
    if (in_array(mb_strtolower($orderInfo['order']['status']), ['wait_for_commit', 'completed'])) {
      $description = Loc::getMessage('PODELI.PAYMENT_TRANSACTION') . $orderInfo['order']['id'];
      $fields = array(
        "PS_STATUS_CODE" => $orderInfo['order']['status'],
        "PS_STATUS_DESCRIPTION" => $description,
        "PS_SUM" => $orderInfo['order']['amount'],
        "PS_RESPONSE_DATE" => new Main\Type\DateTime(),
      );
      if (in_array(mb_strtolower($orderInfo['order']['status']), ['completed'])) {
        $fields['PS_STATUS'] = 'Y';
        if (!$this->isOrderCompletelyRefunded($orderInfo)) {
          $payedStatusBinding = Option::get('podeli.bnpl', 'payed_status_binding');
          if (!empty($payedStatusBinding) && $payedStatusBinding != 0) {
            $order->setField("STATUS_ID", $payedStatusBinding);
          }
          $result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
        } else {
          $order->setField("CANCELED", "Y");
          $cancelStatusBinding = Option::get('podeli.bnpl', 'cancelled_status_binding');
          if (!empty($cancelStatusBinding) && $cancelStatusBinding != 0) {
            $order->setField("STATUS_ID", $cancelStatusBinding);
          }
        }
      }
      Client::logToFile([
        'notification_result' => 'success',
        'orderInfo' => $orderInfo,
      ]);
      $order->save();
      $result->setPsData($fields);
    }
    return $result;
  }

  public function sendResponse(PaySystem\ServiceResult $result, Request $request)
  {
    global $APPLICATION;
    if (!$result->isSuccess()) {
      $APPLICATION->RestartBuffer();
      header("HTTP/1.1 500 " . $result->getErrorMessages()[0]);
      Client::logToFile([
        'status' => 500,
        'errors' => $result->getErrorMessages(),
      ]);
    }
    return '';
  }

  protected function getOrderItems(Payment $payment)
  {
    $articleKey = Option::get('podeli.bnpl', 'article_key');
    $items = [];
    $shipmentCollection = $payment
      ->getCollection()
      ->getOrder()
      ->getShipmentCollection();
    foreach ($shipmentCollection as $shipment) {
      if ($shipment->isSystem()) {
        continue;
      }
      $shipmentItemCollection = $shipment->getShipmentItemCollection();
      foreach ($shipmentItemCollection as $shipmentItem) {
        $basketItem = $shipmentItem->getBasketItem();
        $article = ClientUtils::generateCorrelationId();
        $element = CIBlockElement::GetByID($basketItem->getField('PRODUCT_ID'));
        while ($obElement = $element->GetNextElement()) {
          $properties = $obElement->GetProperties();
          $article = $properties[$articleKey]['VALUE'];
          break;
        }
        if ($basketItem->isBundleChild()) {
          continue;
        }
        if (!$basketItem->getFinalPrice()) {
          continue;
        }
        if ($shipmentItem->getQuantity() <= 0) {
          continue;
        }
        $itemPrice = $basketItem->getPrice();
        if (method_exists($basketItem, "getPriceWithVat")) {
          $itemPrice = $basketItem->getPriceWithVat();
        }
        $item = [
          "id" => $basketItem->getId(),
          "article" => $article,
          "name" => $basketItem->getField('NAME'),
          "quantity" => $shipmentItem->getQuantity(),
          "amount" => number_format(PriceMaths::roundPrecision($itemPrice), 2, '.', ''),
        ];
        $items[] = $item;
      }
      if ($shipment->getPrice()) {
        $items[] = array(
          "id" => $shipment->getId(),
          "article" => 'delivery',
          "name" => $shipment->getDeliveryName(),
          "quantity" => 1,
          "amount" => number_format(PriceMaths::roundPrecision($shipment->getPrice()), 2, '.', ''),
        );
      }
    }
    return $items;
  }

  protected function getNotifyUrl(Payment $payment)
  {
    $context = \Bitrix\Main\Application::getInstance()->getContext();
    $request = $context->getRequest();
    $siteId = $context->getSite();
    $params  = [
      'BX_PAYSYSTEM_CODE' => $payment->getPaymentSystemId(),
      'BX_PAYMENT_ID' => $payment->getId(),
    ];
    $host = $request->getHttpHost();
    if (!$host) {
      $arSite = \Bitrix\Main\SiteTable::getById($siteId)->fetch();
      $host = $arSite['SERVER_NAME'];
    }
    return ($this->isSecure() ? 'https' : 'http') . '://' . $host . '/bitrix/tools/podeli_sale_ps_result.php?' . http_build_query($params);
  }

  protected function isSecure()
  {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || $_SERVER['SERVER_PORT'] == 443;
  }

  protected function getFailUrl(Payment $payment)
  {
    $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
    $failUrl = $this->getBusinessValue($payment, 'FAIL_URL');
    if (!$failUrl) {
      $failUrl = ($this->isSecure() ? 'https' : 'http') . '://' . $request->getHttpHost() . '/bitrix/tools/sale_ps_fail.php';
    }
    return $this->replaceIdPlaceholders($payment, $failUrl);
  }

  protected function getSuccessUrl(Payment $payment)
  {
    $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
    $successUrl = $this->getBusinessValue($payment, 'SUCCESS_URL');
    if (!$successUrl) {
      $successUrl = ($this->isSecure() ? 'https' : 'http') . '://' . $request->getHttpHost() . '/bitrix/tools/sale_ps_success.php';
    }
    return $this->replaceIdPlaceholders($payment, $successUrl);
  }

  protected function replaceIdPlaceholders($payment, $link)
  {
    $replace = [
      '#ID#' => $payment->getId(),
      '#ORDER_ID#' => $payment->getCollection()->getOrder()->getId(),
      '#ORDER_NUMBER#' => $this->getBusinessValue($payment, 'ORDER_NUMBER'),
    ];
    return str_replace(array_keys($replace), array_values($replace), $link);
  }

  protected function createPayment(Payment $payment, $data)
  {
    $result = new PaySystem\ServiceResult();
    $api = $this->initApi($payment);
    try {
      $data['order']['id'] = ClientUtils::prepareOrderId($data['order']['id']);
      $correlationId = ClientUtils::generateCorrelationId();
      $data = Main\Web\Json::encode($data, JSON_UNESCAPED_UNICODE);
      $response = $api->create($data, $correlationId);
      $response['correlation'] = $correlationId;
      $result->setData($response);
    } catch (\Exception $e) {
      $errMsg = $e->getMessage();
      if ($e->getCode() == 422) {
        $errMsg = Loc::getMessage('PODELI.PAYMENT_ERROR_422');
      }
      $result->addError(new Error($errMsg));
      Client::logToFile($e->getMessage());
    }
    return $result;
  }

  protected function getOrderInfo($payment, $orderNumber)
  {
    $result = new PaySystem\ServiceResult();
    $api = $this->initApi($payment);
    try {
      $response = $api->info($orderNumber);
      $result->setData($response);
    } catch (\Exception $e) {
      $result->addError(new Error('Order info error: ' . $e->getMessage()));
      Client::logToFile('Order info error: ' . $e->getMessage());
    }
    return $result;
  }

  protected function commitPayment(Payment $payment, $orderNumber)
  {
    $connection = Application::getConnection();
    $sqlHelper = $connection->getSqlHelper();
    $result = new PaySystem\ServiceResult();
    $api = $this->initApi($payment);
    $paymentShouldPay = number_format(PriceMaths::roundPrecision($payment->getSum()), 2, '.', '');
    $row = $connection
      ->query("SELECT * 
						   FROM podeli_bnpl_request 
					 	   WHERE ORDER_NUMBER = '" . $sqlHelper->forSql($orderNumber) . "'")
      ->fetch();
    $orderItems = Main\Web\Json::decode($row['items']);
    $prepaidAmount = (new ClientWrapper())->calcPrepaidAmount($paymentShouldPay, $orderItems);
    $data = [
      'order' => [
        'amount' => $paymentShouldPay,
        'prepaid_amount' => $prepaidAmount,
      ]
    ];
    try {
      $data = Main\Web\Json::encode($data, JSON_UNESCAPED_UNICODE);
      $response = $api->commit($orderNumber, $data);
      $result->setData($response);
    } catch (\Exception $e) {
      $result->addError(new Error('Commit error: ' . $e->getMessage()));
      Client::logToFile('Commit error: ' . $e->getMessage());
    }
    return $result;
  }

  protected function getPhone(Order $order)
  {
    $phone = '';
    try {
      if ($propUserPhone = $order->getPropertyCollection()->getPhone()) {
        $phone = $propUserPhone->getValue();
      }
    } catch (\Exception $e) {
      return '';
    }
    $phone = preg_replace("#[^\d]#", "", $phone);
    if (!preg_match("#[7|8]{0,1}(\d{10})#", $phone, $match)) {
      return '';
    }
    return '7' . $match[1];
  }

  protected function getFirstName(Order $order)
  {
    $name = '';
    try {
      if ($prop = $order->getPropertyCollection()->getPayerName()) {
        $name = $prop->getValue();
      }
    } catch (\Exception $e) {
      return '';
    }
    $name = explode(' ', $name);
    $position = 0;
    if (count($name) > 1) {
      $position = 1;
    }
    return $name[$position];
  }

  protected function getLastName(Order $order)
  {
    $name = '';
    try {
      if ($prop = $order->getPropertyCollection()->getPayerName()) {
        $name = $prop->getValue();
      }
    } catch (\Exception $e) {
      return '';
    }
    $name = explode(' ', $name);
    if (count($name) < 2) {
      return '';
    }
    return $name[0];
  }

  protected function getEmail(Order $order)
  {
    $email = '';
    try {
      if ($prop = $order->getPropertyCollection()->getUserEmail()) {
        $email = $prop->getValue();
      }
    } catch (\Exception $e) {
      return '';
    }
    return $email;
  }

  protected function saveTransaction(array $data, array $paymentData, Payment $payment)
  {
    $connection = Main\Application::getConnection();
    $sqlHelper  = $connection->getSqlHelper();
    $data = [
      'ORDER_NUMBER' => ClientUtils::prepareOrderId($data['order']['id']),
      'ORDER_ID' => $payment->getCollection()->getOrder()->getId(),
      'PAYMENT_ID' => $payment->getId(),
      'CORRELATION' => $paymentData['correlation'],
      'STATUS' => $paymentData['status'],
      'ITEMS' => Main\Web\Json::encode(['items' => $data['order']['items']], JSON_UNESCAPED_UNICODE),
      'CREATED' => new Main\Type\DateTime(),
      'AMOUNT' => $data['order']['amount'],
      'PAYMENT_SCHEDULE' => Main\Web\Json::encode(isset($paymentData['payment_schedule']) ? $paymentData['payment_schedule'] : null),
    ];
    $tableName = 'podeli_bnpl_request';
    $insert = $sqlHelper->prepareInsert($tableName, $data);
    $sql = "REPLACE INTO " . $sqlHelper->quote($tableName) . "(" . $insert[0] . ") " .
      "VALUES (" . $insert[1] . ")";
    $connection->queryExecute($sql);
  }

  protected function updateTransaction(array $orderInfo, Payment $payment)
  {
    $connection = Main\Application::getConnection();
    $sqlHelper = $connection->getSqlHelper();
    $paymentSchedule = json_encode(isset($orderInfo['paymentSchedule']) ? $orderInfo['paymentSchedule'] : null);
    $status = $orderInfo['order']['status'];
    $connection->query("UPDATE podeli_bnpl_request set STATUS = '" . $sqlHelper->forSql($status) . "',
			PAYMENT_SCHEDULE = '" . $sqlHelper->forSql($paymentSchedule) . "' where PAYMENT_ID = " . $payment->getId() . " 
      and ORDER_NUMBER = '" . $sqlHelper->forSql($orderInfo['order']['id']) . "'");
  }

  protected function initApi(Payment $payment)
  {
    $login = $this->getBusinessValue($payment, 'SHOP_NAME');
    $password = $this->getBusinessValue($payment, 'SHOP_PASSWORD');
    $certPath = $this->getBusinessValue($payment, 'CERT_PATH');
    $keyPath = $this->getBusinessValue($payment, 'KEY_PATH');
    $certValue = $this->getBusinessValue($payment, 'CERT_INPUT');
    $keyValue = $this->getBusinessValue($payment, 'KEY_INPUT');
    $debug = Option::get('podeli.bnpl', 'debug');
    $useCurlHandler = Option::get('podeli.bnpl', 'use_curl_handler');
    return new Client(
      $login,
      $password,
      $certPath,
      $keyPath,
      $certValue,
      $keyValue,
      $this,
      $debug,
      $useCurlHandler
    );
  }

  protected function getCurrentUrl()
  {
    $request = Application::getInstance()->getContext()->getRequest();
    $host = $request->getHttpHost();
    $requestUri = $request->getRequestUri();
    return ($request->isHttps() ? "https://" : "http://") . $host . $requestUri;
  }

  public function info($message)
  {
    if (class_exists('Bitrix\Sale\PaySystem\Logger')) {
      \Bitrix\Sale\PaySystem\Logger::addDebugInfo('PODELI: ' . $message);
    }
  }
}
