<?
namespace Level44;
    use Bitrix\Main\Event;
    use Bitrix\Highloadblock as HL;
    use Bitrix\Main\Type\DateTime;
    use Bitrix\Main\Config\Option;
    use Bitrix\Sale\Order;
    use cKCE;
    use Level44\Enums\DeliveryType;

class CustomKCEClass
{

    public static function SetWaybill(
                        $login,
                        $password,
                        $BtrxOrderId,
                        $RecepientName,
                        $GeoTo,
                        $RecepientFullAddress,
                        $RecepientPhone,
                        $RecepientEmail,
                        $Urgency,
                        $CargoDescription,
                        $CargoPackageQty,
                        $Weight,
                        $GeoFrom,
                        $ClientName,
                        $ClientNameOfficial,
                        $SenderPhone,
                        $SenderEmail,
                        $SenderAddress,
                        $SenderComment,
                        $TakeDate,
                        $TypeOfCargo,
                        $TypeOfPayer,
                        $WayOfPayment,
                        $Items,
                        $SumDost,
                        $SumPayZakaz,
                        $SumNeedPayZakaz,
                        $DeliveryOfCargo,
                        $GUIDPvz,
                        $DeliveryDate,
                        $DeliveryTime,
                        $VATRate
    ) {
        
       // $regionFrom = 'fias-'.$regionFrom;
        $regionTo = 'postcode-'.$regionTo;
        
        //Обновляем информацию о товарах
        $arProd = cKCE::UpdateClientProducts($login, $password, $Items);
        
        //приводим тип доставки в соответствие с накладными
        $DeliveryOfCargo = cKCE::GetDeliveryType($DeliveryOfCargo);

        $rand=rand(0,1598458);
        $DeclaredValueRate = $SumNeedPayZakaz - $SumDost;
        if ($DeliveryDate) {
            $DeliveryDate = cKCE::DateTimeFormat($DeliveryDate);
        }
                if ($SumPayZakaz>0) {
            $COD = floatval($SumNeedPayZakaz) - floatval($SumPayZakaz);
        }else{
            $COD = floatval($SumNeedPayZakaz);
        }
        
        $XmlData = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:car="http://www.cargo3.ru"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema">
         <soap:Header/>
         <soap:Body>
             <car:SaveWaybillOffice>
                 <car:Language/>
                 <car:Login>'.$login.'</car:Login>
                 <car:Password>'.$password.'</car:Password>
                 <car:Company/>
                 <car:Number/>
                 <car:ClientNumber>'.$BtrxOrderId.'-'.$rand.'</car:ClientNumber>
                 <car:OrderData>
                     <car:ClientContact/>';
        if ($DeliveryDate) {
            $XmlData .='<car:DeliveryDate>'.$DeliveryDate.'</car:DeliveryDate>';
                        //<car:DeliveryTime>'.$DeliveryTime.'</car:DeliveryTime>'; 
        }
        

        $XmlData .='<car:Recipient>
                         <car:Client>'.$RecepientName.'</car:Client>
                         <car:Official>'.$RecepientName.'</car:Official>
                         <car:Address>
                             <car:Geography>'.$GeoTo.'</car:Geography>
                             <car:Info>'.$RecepientFullAddress.'</car:Info>
                             <car:Comment></car:Comment>
                             <car:FreeForm>true</car:FreeForm>
                         </car:Address>
                         <car:Phone>'.$RecepientPhone.'</car:Phone>
                         <car:EMail>'.$RecepientEmail.'</car:EMail>
                         <car:Urgency>'.$Urgency.'</car:Urgency>
                         <car:Cargo>
                             <car:CargoDescription>'.$CargoDescription.'</car:CargoDescription>
                             <car:CargoPackageQty>'.$CargoPackageQty.'</car:CargoPackageQty>
                             <car:Weight>'.$Weight.'</car:Weight>                             
                            <car:DeclaredValueRate>'.$DeclaredValueRate.'</car:DeclaredValueRate>
                            <car:COD>'.$COD.'</car:COD>
                            <car:CustomerPrepayment>'.$SumPayZakaz.'</car:CustomerPrepayment>
                         </car:Cargo>';
        
        foreach ($Items as $Item){    
                $Name='';
                foreach ($arProd as $Prod) {
                    if ($Prod['SKU'] == $Item['SKU']) $Name = $Prod['GUID'];
                }
                
                if (!$Item['SKU']) $Item['SKU'] = str_replace('.','',$_SERVER['HTTP_HOST']).$Item['ID'];
                        
                 $XmlData .='<car:Products>
                             <car:Article>'.$Item['SKU'].'</car:Article>  
                             <car:Price>'.$Item['PRICE'].'</car:Price>
                             <car:PackageQty>'.$Item['QTY'].'</car:PackageQty>
                             <car:Qty>'.$Item['QTY'].'</car:Qty>
                             <car:VATRate>'.$VATRate.'</car:VATRate>
                             <car:AssessedValue>'.$Item['PRICE'].'</car:AssessedValue>
                             <car:Comment>'.$Item['NAME'].'</car:Comment>
                         </car:Products>';
        }

        $XmlData .='<car:Products>
                             <car:Article>ДОСТАВКАКСЭ</car:Article>  
                             <car:Price>'.$SumDost.'00</car:Price>
                             <car:PackageQty>1</car:PackageQty>
                             <car:Qty>1</car:Qty>
                             <car:VATRate>'.$VATRate.'</car:VATRate>
                             <car:Comment>Услуга доставки</car:Comment>
                         </car:Products>';
    if ($GUIDPvz) {
        $XmlData .='<car:PVZ>'.$GUIDPvz.'</car:PVZ>';
    }
        $XmlData .='</car:Recipient>                        
                        <car:ReplyEMail>'.$RecepientEmail.'</car:ReplyEMail>
                        <car:ReplySMSPhone>'.$RecepientPhone.'</car:ReplySMSPhone>                        
                     <car:Sender>
                         <car:Client>'.$ClientName.'</car:Client>
                         <car:Official>'.$ClientNameOfficial.'</car:Official>
                         <car:Address>
                             <car:Geography>'.$GeoFrom.'</car:Geography>
                             <car:Info>'.$SenderAddress.'</car:Info>
                             <car:FreeForm>true</car:FreeForm>
                         </car:Address>
                         <car:Phone>'.$SenderPhone.'</car:Phone>
                         <car:EMail>'.$SenderEmail.'</car:EMail>
                     </car:Sender>
                     <car:TakeDate>'.$TakeDate.'</car:TakeDate>
                     <car:TypeOfCargo>'.$TypeOfCargo.'</car:TypeOfCargo>
                     <car:TypeOfPayer>'.$TypeOfPayer.'</car:TypeOfPayer>
                     <car:WayOfPayment>'.$WayOfPayment.'</car:WayOfPayment>
                     <car:Comment>'.$SenderComment.'</car:Comment>
                     <car:DeliveryOfCargo>'.$DeliveryOfCargo.'</car:DeliveryOfCargo>                                                                            
                 </car:OrderData>
                 <car:Office/>
             </car:SaveWaybillOffice>
         </soap:Body>
        </soap:Envelope>';

        //<car:DeliveryDateOf>2020-05-28 00:00:00</car:DeliveryDateOf>

        $search_result = cKCE::GetData($XmlData);

        
        cKCE::CreateLogFile($XmlData,$search_result,__FUNCTION__);

        $sxe = new \SimpleXMLElement($search_result);
        $text = $sxe->children('soap',TRUE);
        $text = $text->children('m',TRUE);

        $WayBills = json_decode(json_encode($text->SaveWaybillOfficeResponse->return->Items));        
        $WayBillID = (string)$WayBills->Value;

        return $WayBillID;
    }

    public static function OnSaleStatusOrderChangeHandler(Event $event)
    {
        if (!class_exists(cKCE::class, 'SetWaybill')) {
            return;
        }

        $allow = Option::get("courierserviceexpress.moduledost", "allowAutoSetWayBill");
        $order = $event->getParameter("ENTITY");
        $orderId = $order->getId();
        $order = Order::load($orderId);

        $waybill = $order->getField('TRACKING_NUMBER');

        //AddMessage2Log($waybill);

        $isnew = $event->getParameter("IS_NEW");

        if (($allow == 'Y') && (empty($waybill))) {
            //AddMessage2Log("yes2");

            $orderstatus = $event->getParameter("VALUE");
            //AddMessage2Log($orderstatus);
            $autostatuses = Option::get("courierserviceexpress.moduledost", "AutoWayBillStatuses");
            $autostatuses = explode(',', $autostatuses);
            //AddMessage2Log($autostatuses);

            if (in_array($orderstatus, $autostatuses)) {

                //AddMessage2Log("yes3");


                /** @var Order $order */
                $moduleId = 'courierserviceexpress.moduledost';

                $orderProps = new \KseOrderProperties($order);
                $properties = $orderProps->propertiesByCode;
                $TotalQty = 1;//?????
                $ordersTotalSum = $order->getPrice() - $order->getDeliveryPrice();
                $ordersTotalWeight = $orderProps->getTotalWeight();
                if (empty($ordersTotalWeight)) {
                    $ordersTotalWeight = Option::get("courierserviceexpress.moduledost", "massa");
                }
                $ordersTakingAmountSum = $orderProps->getTakingAmount();
                $orderZIP = $orderProps->propertiesByCode['ZIP'];
                $matterCategoryList[] = $orderProps->getMatter();
                $delivery_address = $orderProps->getDeliveryAddress();
                $delivery_required_date = date('Y-m-d', strtotime('+' . Option::get($moduleId, "dateZaboraDays") . ' day'));//дата доставки
                //$delivery_required_date = date('Y-m-d', strtotime($orderProps->getRequiredFinishDatetime()));
                $delivery_required_start_time = date('H:i', strtotime($orderProps->getRequiredStartDatetime()));
                $delivery_required_finish_time = date('H:i', strtotime($orderProps->getRequiredFinishDatetime()));
                $delivery_recipient_name = trim("{$properties['LAST_NAME']} {$properties['FIRST_NAME']} {$properties['SECOND_NAME']}");
                $delivery_recipient_phone = $orderProps->getRecipientPhone();
                $delivery_note = $orderProps->getNoteWithPrefix();
                $pickup_date = date('Y-m-d', strtotime('+' . Option::get($moduleId, "dateZaboraDays") . ' day'));

                $TovarSku = Option::get("courierserviceexpress.moduledost", "TovarSku");
                $TPSku = Option::get("courierserviceexpress.moduledost", "TPSku");

                //Собираем данные для формирования накладной
                $login = Option::get("courierserviceexpress.moduledost", "login");
                $password = Option::get("courierserviceexpress.moduledost", "pass");
                $RecepientName = $delivery_recipient_name;
                $GeoTo = 'postcode-' . $orderZIP;
                $RecepientFullAddress = $delivery_address;
                $RecepientPhone = $delivery_recipient_phone;
                $Urgency = Option::get("courierserviceexpress.moduledost", "urgency");
                $CargoDescription = '';
                $CargoPackageQty = $TotalQty;
                $ClientName = htmlspecialchars(Option::get($moduleId, "CompanyName"), ENT_NOQUOTES);
                $ClientNameOfficial = htmlspecialchars(Option::get($moduleId, "SenderName"), ENT_NOQUOTES);
                $Weight = $ordersTotalWeight;
                $ZIP = \KseService::GetZipCode(Option::get("courierserviceexpress.moduledost", "GorodZaboraGruza"));
                $GeoFrom = 'postcode-' . $ZIP;
                $SenderAddress = Option::get($moduleId, "AdresZaboraGruza");
                $SenderComment = $delivery_note;
                $TakeDate = $pickup_date . 'T' . '00:00:00';//по умолчанию!
                $TypeOfCargo = Option::get("courierserviceexpress.moduledost", "TypeOfCargo");
                $TypeOfPayer = Option::get("courierserviceexpress.moduledost", "PayerCode");
                $WayOfPayment = Option::get("courierserviceexpress.moduledost", "PaymentMethod");
                $BtrxOrderId = $orderId;
                $SenderPhone = Option::get($moduleId, "SenderContactPhone");
                $SenderEmail = Option::get($moduleId, "SenderContactEmail");
                $VATRate = Option::get("courierserviceexpress.moduledost", "KCEVat");
                //Курьер до дверей
                $DeliveryOfCargo = '0';

                $deliveryType = \Level44\Delivery::getType($order->getField('DELIVERY_ID'));

                //Получаем код плательщика
                $PayerCodes = cKCE::GetPayerCode($login, $password);
                if (is_array($PayerCodes)) {
                    [, $sender, $recipient] = array_map(fn($item) => $item['mKey'], $PayerCodes);

                    if (isset($sender) && $deliveryType === DeliveryType::Courier) {
                        $TypeOfPayer = $sender;
                    } elseif (isset($recipient) && $deliveryType === DeliveryType::CourierFitting) {
                        $TypeOfPayer = $recipient;
                    }
                }

                $VATRates = static::GetVATRates($login, $password);

                if (is_array($VATRates)) {
                    $current = current(array_filter($VATRates, fn($item) => $item['mValue'] === '5%'));
                    if (isset($current['mKey'])) {
                        $VATRate = $current['mKey'];
                    }
                }

                //Получаем информацию о товарах из корзины
                $res = \CSaleBasket::GetList([], ["ORDER_ID" => $orderId]);
                $i = 0;

                //Get Recepient Email
                $order = \CSaleOrder::GetByID($orderId);
                $RecepientEmail = $order['USER_EMAIL'];
                $orderProps = \CSaleOrderPropsValue::GetOrderProps($orderId);
                $EmailID = Option::get("courierserviceexpress.moduledost", "inputEmail");
                $GUIDPvzOptionsFiz = '';
                $GUIDPvzOptionsUr = '';
                AddMessage2Log($DeliveryOfCargo);
                while ($prop = $orderProps->Fetch()) {

                    //Получаем Email
                    if ($prop['PROP_ID'] == $EmailID) {
                        $RecepientEmail = $prop['VALUE'];
                    }
                    //Получаем GUIDPVZ физиков (если он есть)
                    if ($prop['PROP_ID'] == $GUIDPvzOptionsFiz) {
                        $GUIDPvz = $prop['VALUE'];
                        $DeliveryOfCargoPVZ = Option::get("courierserviceexpress.moduledost", "pvz");
                    }

                    //Получаем GUIDPVZ юриков (если он есть)
                    if ($prop['PROP_ID'] == $GUIDPvzOptionsUr) {
                        $GUIDPvz = $prop['VALUE'];
                        $DeliveryOfCargoPVZ = Option::get("courierserviceexpress.moduledost", "pvz");
                    }
                }
                $RecepientEmail = $properties['EMAIL'];

                if ($DeliveryOfCargoPVZ) $DeliveryOfCargo = $DeliveryOfCargoPVZ;

                while ($arItem = $res->Fetch()) {

                    //Получаем артикул товара (торгового предложения) для добавления в накладнкую
                    $arFilter2 = ["ID" => $arItem['PRODUCT_ID']];
                    $res2 = \CIBlockElement::GetList([], $arFilter2);
                    if ($ob2 = $res2->GetNextElement()) {
                        $arProps2 = $ob2->GetProperties(); // свойства элемента

                        //По умолчанию берем артикул как XML_ID товара (чтобы он был непустым в любом случае)
                        $Items[$i]['SKU'] = $arItem['PRODUCT_XML_ID'];

                        //Пробегаем по свойствам, отмеченным как АРТИКУЛ, и записываем значения в итоговый массив
                        foreach ($arProps2 as $Prop2) {
                            if ($Prop2['ID'] == $TovarSku) $Items[$i]['SKU'] = $Prop2['VALUE'];
                            if ($Prop2['ID'] == $TPSku) $Items[$i]['SKU'] = $Prop2['VALUE'];
                        }
                    }

                    //Формируем массив с данынми по товару
                    $Items[$i]['NAME'] = $arItem['NAME'];
                    $Items[$i]['PRICE'] = $arItem['PRICE'];
                    $Items[$i]['CURR'] = $arItem['CURRENCY'];
                    $Items[$i]['QTY'] = $arItem['QUANTITY'];
                    $Items[$i]['ID'] = $arItem['PRODUCT_ID'];
                    $Items[$i]['UNIT'] = $arItem['MEASURE_NAME'];
                    $i++;
                }

                $OrderData = \CSaleOrder::GetByID($orderId);
                $SumDost = number_format($OrderData['PRICE_DELIVERY'], 2, '.', '');
                $SumPayZakaz = $OrderData['SUM_PAID'];

                $SumNeedPayZakaz = $OrderData['PRICE'];

                $DeliveryDate = '';//$delivery_required_date;
                $DeliveryTime = '';//$delivery_required_start_time.' - '.$delivery_required_finish_time;

                if (($login) && ($password) && ($BtrxOrderId) && ($RecepientName) && ($GeoTo) && ($RecepientFullAddress) && ($RecepientPhone) && ($Urgency) && ($CargoPackageQty) && ($Weight) && ($GeoFrom) && ($ClientName) && ($SenderPhone) && ($TakeDate) && ($TypeOfCargo) && ($Items)) {
                    //Формируем накладную и получаем ее номер для отслеживания статусов
                    $WayBillID = static::SetWaybill(
                        $login,
                        $password,
                        $BtrxOrderId,
                        $RecepientName,
                        $GeoTo,
                        $RecepientFullAddress,
                        $RecepientPhone,
                        $RecepientEmail,
                        $Urgency,
                        $CargoDescription,
                        $CargoPackageQty,
                        $Weight,
                        $GeoFrom,
                        $ClientName,
                        $ClientNameOfficial,
                        $SenderPhone,
                        $SenderEmail,
                        $SenderAddress,
                        $SenderComment,
                        $TakeDate,
                        $TypeOfCargo,
                        $TypeOfPayer,
                        $WayOfPayment,
                        $Items,
                        $SumDost,
                        $SumPayZakaz,
                        $SumNeedPayZakaz,
                        $DeliveryOfCargo,
                        $GUIDPvz,
                        $DeliveryDate,
                        $DeliveryTime,
                        $VATRate
                    );
                    $result = $WayBillID;

                    //AddMessage2Log($result);

                    if ($WayBillID) {

                        $OrderData = \CSaleOrder::getByID($orderId);

                        $UpdTrack['TRACKING_NUMBER'] = $WayBillID;
                        $UpdTrack['DELIVERY_DOC_NUM'] = $WayBillID;
                        $UpdTrack['DELIVERY_DOC_DATE'] = new DateTime();

                        $OrderUpd = \CSaleOrder::Update($orderId, $UpdTrack);

                        //Записываем накладную в список
                        $hl = \Bitrix\Highloadblock\HighloadBlockTable::getList(['filter' => ['TABLE_NAME' => 'ksewaybills']])->fetch();

                        if (!empty($hl['ID'])) {
                            $hlblock = HL\HighloadBlockTable::getById($hl['ID'])->fetch();
                            $entity = HL\HighloadBlockTable::compileEntity($hlblock);
                            $entity_data_class = $entity->getDataClass();

                            //Если такая накладная уже есть в базе, то не пишем её
                            $rsData = $entity_data_class::getList([
                                "select" => ["*"],
                                "order"  => ["ID" => "ASC"],
                                "filter" => ["UF_WAYBILLID" => $WayBillID]
                            ]);
                            $rsData = $rsData->fetch();
                            //pr ($rsData);
                            if (!$rsData) {
                                $HBLdata = [
                                    "UF_WAYBILLID"    => $WayBillID,
                                    "UF_ORDERID"      => $orderId,
                                    "UF_WAYBILL_DATE" => $UpdTrack['DELIVERY_DOC_DATE'],
                                    "UF_ADR_OTPR"     => Option::get($moduleId, "AdresZaboraGruza"),
                                    "UF_KSE_WEIGHT"   => $Weight,
                                    "UF_KSE_QTY"      => $CargoPackageQty
                                ];
                                $HBLresult = $entity_data_class::add($HBLdata);
                            }
                        }
                        $result = $WayBillID;
                        //AddMessage2Log($result);
                    } else {
                        //AddMessage2Log('KSE_ORDER_ERROR_1');
                    }


                } else {
                    //AddMessage2Log('KSE_ORDER_ERROR_2');
                }
            } else {
                //AddMessage2Log('checked=NO!');
            }
        }
    }

    public static function GetVATRates($login,$password) {

        $XmlData = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://www.cargo3.ru">
                     <soap:Header/>
                     <soap:Body>
                     <ns1:GetReferenceData>
                     <ns1:login>'.$login.'</ns1:login>
                     <ns1:password>'.$password.'</ns1:password>
                     <ns1:parameters>
                     <ns1:Key>parameters</ns1:Key>
                     <ns1:List>
                     <ns1:Key>Reference</ns1:Key>
                     <ns1:Value>VATRates</ns1:Value>
                     <ns1:ValueType>string</ns1:ValueType>
                     </ns1:List>
                     </ns1:parameters>
                     </ns1:GetReferenceData>
                     </soap:Body>
                    </soap:Envelope>';

        $result = cKCE::GetData($XmlData);
        $text = cKCE::FormatXml($result);

        return $text;

    }

    public static function OnSaleStatusShipmentChangeHandler(Event $event)
    {
        $shipment = $event->getParameter('ENTITY');
        $value = $event->getParameter('VALUE');

        $orderId = $shipment->getField('ORDER_ID');

        switch ($value) {
            case 'KD':
                (new \CSaleOrder())->StatusOrder($orderId, 'DE');
                break;
            case 'KC':
                (new \CSaleOrder())->StatusOrder($orderId, 'F');
                break;
            case 'KR':
                (new \CSaleOrder())->StatusOrder($orderId, 'RT');
                break;
        }
    }
}