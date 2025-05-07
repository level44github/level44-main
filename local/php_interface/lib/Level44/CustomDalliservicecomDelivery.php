<?php

namespace Level44;

use CCatalogProductSet;
use CIBlockElement;
use CModule;
use COption;
use CSaleBasket;
use CSaleLocation;
use CSaleOrder;
use DalliservicecomDelivery;
use DalliservicecomDeliveryDB;
use DateTime;
use Error;
use Exception;

class CustomDalliservicecomDelivery extends DalliservicecomDelivery
{
    public static function OnSaleBeforeStatusOrderChange($order)
    {
        if ($order->getField('STATUS_ID') != COption::GetOptionString('dalliservicecom.delivery', 'SEND_ON_STATUS'))
            return false;
        $dbRes = \Bitrix\Sale\PropertyValueCollection::getList([
            'select' => ['*'],
            'filter' => [
                '=ORDER_ID' => $order->getId(),
            ]
        ]);
        $arOrder = CSaleOrder::GetByID($order->getId());

        if (isset($order))
            $collection = $order->getShipmentCollection();
        if(!empty($collection[0])) {
            $collection = $collection[0]->getShipmentItemCollection();
            foreach ($collection as $shipmentItem) {
                $productId = $shipmentItem->getProductId();
                try {
                    $shipments = $shipmentItem->getShipmentItemStoreCollection()->getShipmentItem()->toArray()['STORES'];
                    if (!empty($shipments)) {
                        foreach ($shipments as $shipment) {
                            $governmentCodes[$productId][] = $shipment['MARKING_CODE'];
                        }
                    }
                } catch (Error $ex) {
                }
            }
        }

        $result['id'] = COption::GetOptionString(self::MODULE_ID, "ID_OR_NUMBER") == 'number' ? $arOrder['ACCOUNT_NUMBER'] : $arOrder['ID'];

        $delivery = $arOrder['DELIVERY_ID'];
        $address = '';
        while ($item = $dbRes->fetch()) {
            if ($item['CODE'] === COption::GetOptionString(self::MODULE_ID, "PHONE"))
                $result['phone'] = $item['VALUE'];
            if ($item['CODE'] === COption::GetOptionString(self::MODULE_ID, "ZIP"))
                $result['zipcode'] = $item['VALUE'];

            $separate_addr = COption::GetOptionString(self::MODULE_ID, 'SEPARATE_ADDR') == "Y" ? true : false;
            $separate_fio = COption::GetOptionString(self::MODULE_ID, 'SEPARATE_FIO') == "Y" ? true : false;
            $separate_time = COption::GetOptionString(self::MODULE_ID, 'SEPARATE_TIME') == "Y" ? true : false;
            $address_prop = COption::GetOptionString(self::MODULE_ID, "ADDRESS");
            $person_prop = COption::GetOptionString(self::MODULE_ID, "PERSON");

            if ($separate_addr) {
                $street_prop = COption::GetOptionString(self::MODULE_ID, "STREET");
                $house_prop = COption::GetOptionString(self::MODULE_ID, "HOUSE");
                $housing_prop = COption::GetOptionString(self::MODULE_ID, "HOUSING");
                $flat_prop = COption::GetOptionString(self::MODULE_ID, "FLAT");
            }
            if ($separate_fio) {
                $surname_prop = COption::GetOptionString(self::MODULE_ID, "SURNAME");
                $name_prop = COption::GetOptionString(self::MODULE_ID, "NAME");
                $patronymic_prop = COption::GetOptionString(self::MODULE_ID, "PATRONYMIC");
            }
            if ($separate_time) {
                $time_min_prop = COption::GetOptionString(self::MODULE_ID, "TIME_MIN");
                $time_max_prop = COption::GetOptionString(self::MODULE_ID, "TIME_MAX");
            }

            if ($separate_addr && (in_array($delivery, ['dalli_service:dalli_courier', 'dalli_service:sdek_courier', 'dalli_service:dalli_cfo', 'dalli_service:dalli_express']) || strpos($delivery, 'dalli_service:pochta') !== false)) {
                if ($item["CODE"] == $street_prop) $address .= GetMessage('STREET') . ' ' . $item["VALUE"] . ($zm_i < 10 ? ", " : " ");
                if ($item["CODE"] == $house_prop) $address .= GetMessage('HOUSE') . ' ' . $item["VALUE"] . ($zm_i < 10 ? ", " : " ");
                if ($item["CODE"] == $housing_prop) $address .= GetMessage('HOUSING') . ' ' . $item["VALUE"] . ($zm_i < 10 ? ", " : " ");
                if ($item["CODE"] == $flat_prop) $address .= GetMessage('FLAT') . ' ' . $item["VALUE"] . ($zm_i < 10 ? ", " : " ");
                $zm_i++;
            } else {
                if ($item["CODE"] == $address_prop) $address = $item["VALUE"];
            }
            $result['address'] = $address;
            if ($separate_fio) {
                if ($item["CODE"] == $surname_prop) $surname = $item["VALUE"];
                if ($item["CODE"] == $name_prop) $name = $item["VALUE"];
                if ($item["CODE"] == $patronymic_prop) $patronymic = $item["VALUE"];
            } else {
                if ($item["CODE"] == $person_prop) $fio = $item["VALUE"];
            }


            if ($item['CODE'] === 'LOCATION') {
                $cityArr = CSaleLocation::GetByID($item['VALUE'], LANGUAGE_ID);
                $result['city'] = $cityArr["CITY_NAME"];
                $region = $cityArr["REGION_NAME"];
            }
            if ($separate_time) {
                if ($item['CODE'] === $time_min_prop) {
                    $result['time_min'] = $item['VALUE'];
                }
                if ($item['CODE'] === $time_max_prop) {
                    $result['time_max'] = $item['VALUE'];
                }
            } else {
                if ($item['ORDER_PROPS_ID'] === COption::GetOptionString(self::MODULE_ID, "TIME_PROP_ID")) {
                    $time_min_max = $item['VALUE'];
                }
            }
            if ($item['CODE'] === COption::GetOptionString(self::MODULE_ID, "Date_Delivery"))
                $result['date'] = $item['VALUE'];
        }
        if ($separate_fio) {
            $result['fio'] = $surname . ' ' . $name . ' ' . $patronymic;
        } else {
            $result['fio'] = $fio;
        }
        if (!isset($result['date']) || !$result['date']) {
            $d = strtotime("+1 day");
            $result['date'] = date("Y-m-d", $d);
        } elseif (strpos($result['date'], '.') !== false) {
            $result['date'] = mb_substr($result['date'], 0, 10);
            $result['date'] = DateTime::createFromFormat('d.m.Y', $result['date'])->format('Y-m-d');
        }
        $result['priced'] = $arOrder['PRICE_DELIVERY'];
        $result['price'] += $result['priced'];
        $result['instruction'] = $arOrder['USER_DESCRIPTION'];

        switch ($delivery) {
            case "dalli_service:dalli_courier":
                if ($result['city'] == GetMessage('MOSKOW') || strpos($region, GetMessage('MOSKOW_REGION')) !== false) {
                    $result['service'] = 1;
                } elseif ($result['city'] == GetMessage('LENINGRAD') || strpos($region, GetMessage('LENINGRAD_REGION')) !== false) {
                    $result['service'] = 11;
                }
                break;
            case "dalli_service:sdek_courier":
                $result['service'] = 10;
                break;
            case "dalli_service:dalli_cfo":
                $result['service'] = 22;
                break;
            case "dalli_service:dalli_pvz":
                switch ($result['city']) {
                    case  GetMessage('MOSKOW'):
                        $result['service'] = 4;
                        break;
                    case GetMessage('LENINGRAD'):
                        $result['service'] = 12;
                        break;
                    default:
                        $result['service'] = 23;
                        break;
                }
                break;
            case "dalli_service:sdek_pvz":
                $result['service'] = 9;
                break;
            case "dalli_service:boxberry_pvz":
                $result['service'] = 13;
                break;
            case "dalli_service:boxberry_courier":
                $result['service'] = 26;
                break;
            case "dalli_service:5post_pvz":
                $result['service'] = 20;
                break;
            case "dalli_service:pickpoint_pvz":
                $result['service'] = 15;
                break;
            case "dalli_service:pochta_19_1":
                $result['service'] = '19_1';
                break;
            case "dalli_service:pochta_19_2":
                $result['service'] = '19_2';
                break;
            case "dalli_service:pochta_19_3":
                $result['service'] = '19_3';
                break;
            case "dalli_service:pochta_19_4":
                $result['service'] = '19_4';
                break;
            default:
                if (COption::GetOptionString(self::MODULE_ID, "AUTOSEND_ONLY_DS") === 'YES') {
                    return false;
                }
            /*            case "dalli_service:pickup_pvz":
                            $result['service'] = 10;
                            break;*/
        }
        $res = CSaleBasket::GetList(array(), array("ORDER_ID" => $order->getId()));
        $i = 0;
        while ($arItem = $res->Fetch()) {
            if ((int)$arItem['TYPE'] === 1) {    //���� ��������
                $arSets = CCatalogProductSet::getAllSetsByProduct((int)$arItem['PRODUCT_ID'], 1);  //�����, ����� ������ � ���� ������
                if (!empty($arSets)) {
                    foreach (array_shift($arSets)['ITEMS'] as $item) {
                        $ignored_items[] = $item['ITEM_ID'];
                    }
                }
            }
            //������� �������� �������, ������� ������ � ��������
            if (!empty($ignored_items)) {
                $ignored_key = array_search($arItem["PRODUCT_ID"], $ignored_items);
                if ($ignored_key !== false) {
                    unset($ignored_items[$ignored_key]);
                    continue;
                }
            }
            if ($arItem["WEIGHT"] == '' || $arItem["WEIGHT"] == 0)
                $arItem["WEIGHT"] = (float)COption::GetOptionString(self::MODULE_ID, 'WEIGHT_DEFAULT') * 1000;
            if ($arItem["WEIGHT"] == '' || $arItem["WEIGHT"] == 0)
                $arItem["WEIGHT"] = 0.1;
            $result['price'] += $arItem['PRICE'] * (int)$arItem['QUANTITY'];
            if ($arItem["PRODUCT_ID"]) {
                if (empty($governmentCodes[$arItem["PRODUCT_ID"]])) {
                    $governmentCodes[$arItem["PRODUCT_ID"]] = [''];
                }
                foreach ($governmentCodes[$arItem["PRODUCT_ID"]] as $governmentCode) {
                    $i++;
                    if (floor($arItem['QUANTITY']) != $arItem["QUANTITY"]) {  //���� ���������� - �������, �� �������� ��� �� ��������� ������
                        $result['item_retprice_' . $i] = $arItem['PRICE'] * $arItem["QUANTITY"];
                        $arItem['QUANTITY'] = 1;
                    } else {
                        $result['item_retprice_' . $i] = $arItem['PRICE'];
                    }
                    if(count($governmentCodes[$arItem["PRODUCT_ID"]])>1){
                        $arItem['QUANTITY'] = 1;
                    }
                    $result['item_quantity_' . $i] = (int)$arItem['QUANTITY'];
                    $result['item_mass_' . $i] = $arItem['WEIGHT'] / 1000;
                    $result['item_vatrate_' . $i] = $arItem['VAT_RATE'] * 100;
                    $result['item_name_' . $i] = $arItem['NAME'];
                    $result['item_governmentCode_' . $i] = $governmentCode;
                    CModule::IncludeModule("iblock");
                    $zm_res_item = CIBlockElement::GetByID($arItem["PRODUCT_ID"]);
                    if ($zm_ar_item = $zm_res_item->GetNext()) {
                        $res_item_props = CIBlockElement::GetProperty($zm_ar_item['IBLOCK_ID'], $arItem["PRODUCT_ID"], "sort", "asc", array());
                        while ($ar_item_prop = $res_item_props->GetNext()) {
                            if ($ar_item_prop['CODE'] == COption::GetOptionString(self::MODULE_ID, 'ARTICLE')) {
                                $result['item_article_' . $i] = $ar_item_prop['VALUE'];
                            }
                            if ($ar_item_prop['CODE'] == COption::GetOptionString(self::MODULE_ID, 'BARCODE')) {
                                $result['item_barcode_' . $i] = $ar_item_prop['VALUE'];
                            }
                        }
                    }
                }
            }
        }
        if (!isset($time_min_max)) {
            $time_min_max = COption::GetOptionString(self::MODULE_ID, "TIME_DEFAULT");
        }
        if (strlen(trim($time_min_max)) > 0) {
            $timeArr = explode('-', $time_min_max);
            if (!$result['time_min'])
                $result['time_min'] = $timeArr[0];
            if (!$result['time_max'])
                $result['time_max'] = $timeArr[1];
        }
        $paytype_cash_arr = unserialize(COption::GetOptionString(self::MODULE_ID, "PAYTYPE_CASH"));
        $paytype_card_arr = unserialize(COption::GetOptionString(self::MODULE_ID, "PAYTYPE_CARD"));
        $paytype_no_arr = unserialize(COption::GetOptionString(self::MODULE_ID, "PAYTYPE_NO"));
        $paySystemId = $arOrder["PAY_SYSTEM_ID"];
        if (is_array($paytype_cash_arr) && in_array($paySystemId, $paytype_cash_arr))
            $result['paytype'] = 'CASH';
        if (is_array($paytype_card_arr) && in_array($paySystemId, $paytype_card_arr))
            $result['paytype'] = 'CARD';
        if (is_array($paytype_no_arr) && in_array($paySystemId, $paytype_no_arr) && ($arOrder['PAYED'] === 'Y'))
            $result['paytype'] = 'NO';
        if (empty($result['paytype']))
            $result['paytype'] = 'CASH';
        $result['inshprice'] = $result['price'];
        $result['place_quantity'] = 1;
        $errors = self::sendOrder2DS($result, true);
        if (!empty($errors)) {
            $errors = str_replace('<br/>', ', ', $errors);
            $errors = trim($errors, ', ');
            $error = new \Bitrix\Main\Error($errors);
            $resultError = \Bitrix\Sale\ResultError::create($error);
            $result = new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::ERROR, $resultError, 'sale');
            return $result;
        }
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS
        );
    }

    static public function sendOrder2DS($data = null, $nocheck = false)
    {
        if ($nocheck)
            goto send;
        if ($_POST['action'] != 'send2dalli')
            return;
        if ($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['action'] == 'send2dalli' && defined('ADMIN_SECTION')) {
            if (!check_bitrix_sessid())
                return;
            global $APPLICATION;
            $APPLICATION->RestartBuffer();
            $arrItems = array();
            $items = '';
            unset ($_POST['action']);
            unset ($_POST['sessid']);
            $data = $_POST;
        }
        send:
        if (!empty($data)) {
            foreach ($data as $key => &$param) {
                $param = htmlspecialchars($param);
                if (strpos($key, 'item_quantity_') !== false) {
                    $index = substr($key, 14);
                    $arrItems[$index]['quantity'] = $param;
                }
                if (strpos($key, 'item_article_') !== false) {
                    $index = substr($key, 13);
                    $arrItems[$index]['article'] = $param;
                }
                if (strpos($key, 'item_barcode_') !== false) {
                    $index = substr($key, 13);
                    $arrItems[$index]['barcode'] = $param;
                }
                if (strpos($key, 'item_mass_') !== false) {
                    $index = substr($key, 10);
                    $arrItems[$index]['mass'] = $param;
                }
                if (strpos($key, 'item_retprice_') !== false) {
                    $index = substr($key, 14);
                    $arrItems[$index]['retprice'] = $param;
                }
                if (strpos($key, 'item_name_') !== false) {
                    $index = substr($key, 10);
                    $arrItems[$index]['name'] = $param;
                }
                if (strpos($key, 'item_governmentCode_') !== false) {
                    $index = substr($key, 20);
                    $arrItems[$index]['governmentCode'] = $param;
                }
                if (strpos($key, 'item_vatrate_') !== false) {
                    $index = substr($key, 13);
                    $arrItems[$index]['vatrate'] = $param;
                }
            }
            unset($param);
            if (count($arrItems) > 0) {
                foreach ($arrItems as $item) {
                    $items .= sprintf(
                        "<item quantity='%s' mass='%s' retprice='%s' barcode='%s' article='%s' governmentCode='%s' VATrate='%s'>%s</item>",
                        $item["quantity"],
                        $item["mass"],
                        $item["retprice"],
                        $item["barcode"],
                        $item["article"],
                        $item["governmentCode"],
                        $item["vatrate"],
                        $item["name"]
                    );
                }
            }

            $items = "<items>" . $items . "</items>";
            $token = COption::GetOptionString(self::MODULE_ID, "DALLI_TOKEN");
            $items = str_replace("mass=", "weight=", $items);
            if(!isset($data['pvz'])) {
                $pvzCode = DalliservicecomDeliveryDB::GetPvzList(['address' => $data['address']])->Fetch()['code'];
            }
            else{
                $pvzCode = $data['pvz'];
            }
            if (isset($pvzCode))
                $data['pvzCode'] = "<pvzcode>$pvzCode</pvzcode>";
            else
                $data['pvzCode'] = '';
            if(COption::GetOptionString(self::MODULE_ID, "USE_DELIVERYSET") === 'Y'){//���� ������������ deliveryset
                $nds = (int)COption::GetOptionString(self::MODULE_ID, "NDS_DELIVERY");   //���� ��� �� ��������
                if(COption::GetOptionString(self::MODULE_ID, "PAY_FOR_DELIVERY_IF_REFUSED") === 'Y'){//���� ���� ������ �� �������� � ������ ������
                    $returnPrice = $data['priced'];
                }
                else{
                    $returnPrice = 0;
                }
                $deliveryset = '<deliveryset above_price="'.$data['priced'].'" return_price="'.$returnPrice.'" VATrate="'.$nds.'"></deliveryset>';
            }
            else{
                $deliveryset = '';
            }
            if (strpos($data['service'], '19_') !== false) {//���� �����
                $type = substr($data['service'], 3, 1);
                if($data['paytype']==='NO'){
                    $data['price'] = 0;
                }
                $xml_data = '<?xml version="1.0" encoding="UTF-8"?>
                <rupostcreate module="bitrix">
                  <auth token="' . $token . '"></auth>
                  <order number="' . $data['id'] . '">
                    <receiver>
                      <phone>' . $data['phone'] . '</phone>
                      <town>' . $data['city'] . '</town>
                      <address>' . $data['address'] . '</address>
                       <zipcode>' . $data['zipcode'] . '</zipcode>
                      <person>' . $data['fio'] . '</person>
                    </receiver>
                    <type>' . $type . '</type>
                     <price>' . $data['price'] . '</price> 
                     <inshprice>' . $data['inshprice'] . '</inshprice> 
                     <instruction>' . $data['instruction'] . '</instruction> 
                  </order>
                </rupostcreate>';
            } else {
                $xml_data = <<<EOD
                <?xml version="1.0" encoding="UTF-8"?>
                <basketcreate module='bitrix'>
                    <auth token="$token" />
                    <order number="{$data['id']}">
                    <receiver>
                        <town>{$data['city']}</town>
                        <address>{$data['address']}</address>
                        {$data['pvzCode']}
                        <zipcode>{$data['zipcode']}</zipcode>
                        <person>{$data['fio']}</person>
                        <phone>{$data['phone']}</phone>
                        <date>{$data['date']}</date>
                        <time_min>{$data['time_min']}</time_min>
                        <time_max>{$data['time_max']}</time_max>
                    </receiver>
                    <service>{$data['service']}</service>
                    <quantity>{$data['place_quantity']}</quantity>
                    <paytype>{$data['paytype']}</paytype>
                    <priced>{$data['priced']}</priced>
                    <price>{$data['price']}</price>
                    <inshprice>{$data['inshprice']}</inshprice>
                    <instruction>{$data['instruction']}</instruction>
                    $deliveryset
                    $items
                    </order>
                </basketcreate>
EOD;
            }

            $arResult = self::send_xml($xml_data, 0);

            $errors = "";
            try {
                if (isset($data['id']) && (!empty($data['id']))) {
                    $order = \Bitrix\Sale\Order::load($data['id']);
                }
                if (isset($order)) {
                    $collection = $order->getShipmentCollection();
                }
            } catch (Exception $e) {
            }
            if (strpos($data['service'], '19_') !== false) {//���� �����
                $result = $arResult["rupostcreate"]["#"]["order"][0]["#"];
                if ($result["error"][0]['@']['errorCode'] === '0') {    //���� �� ������
                    unset($result["error"]);
                    $result["success"][0]["@"]["barcode"] = $arResult["rupostcreate"]["#"]["order"][0]["@"]['barcode'];
                }
            } else {
                $result = $arResult["basketcreate"]["#"]["order"][0]["#"];
            }
            if (isset($result["error"])) {
                $errorStatus = COption::GetOptionString('dalliservicecom.delivery', 'SET_ERROR_STATUS');
                if (isset($errorStatus, $collection) && ($errorStatus !== 'NO_NEED')) {
                    $collection[0]->setField('STATUS_ID', COption::GetOptionString('dalliservicecom.delivery', 'SET_ERROR_STATUS'));
                    $collection[0]->save();
                }
                if(!empty($result["error"])) {
                    foreach ($result["error"] as $error) {
                        $errors .= $error["@"]["errorMessage"] . "<br/>";
                    }
                }
                if ($nocheck)
                    return $errors;
                exit(json_encode(
                    array(
                        "error" => 1,
                        "errormsg" => $errors,
                        "barcode" => ""
                    )
                ));
            }
            {
                echo json_encode(
                    array(
                        "error" => 0,
                        "errormsg" => strpos($data['service'], '19_') != false ? GetMessage('ORDER_ADDED_TO_LK').'<br>'.GetMessage('TRACKING_NUMBER').':' : GetMessage('ORDER_ADDED_TO_CART').'<br>'.GetMessage('TRACKING_NUMBER').':',
                        "barcode" => $result["success"][0]["@"]["barcode"]
                    )
                );
                if (isset($collection[0])) {
                    $collection[0]->setField('DELIVERY_DOC_NUM', $result["success"][0]["@"]["barcode"]);
                    $collection[0]->setField('TRACKING_NUMBER', $result["success"][0]["@"]["barcode"]);
                    $successStatus = COption::GetOptionString('dalliservicecom.delivery', 'SET_SUCCESS_STATUS');
                    if (isset($successStatus) && ($successStatus !== 'NO_NEED')) {
                        $collection[0]->setField('STATUS_ID', COption::GetOptionString('dalliservicecom.delivery', 'SET_SUCCESS_STATUS'));
                    }
                    $collection[0]->save();
                    if ($nocheck)
                        return '';
                }
            }
            die();
        }
    }
}