<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PropertyValueCollectionBase;
use Level44\Event;

require_once __DIR__ . '/autoload.php';

global $MESS;

if (\Level44\Base::isEnLang()) {
    $MESS['IPOLSDEK_DELIV_ERR_NOPVZ'] = "To place an order, please specify an SDEK pick-up point.";
    $MESS['IPOLSDEK_FRNT_CHOOSEPICKUP'] = "Select a pick-up point";
} else {
    $MESS['VBCH_CLPAY_MM_DESC'] = Loc::getMessage("VBCH_CLPAY_MM_DESC");
}

\Level44\Base::customRegistry();
Event\Handlers::register();

// События которые срабатывают при создании или изменении элемента инфоблока
//AddEventHandler("iblock", "OnAfterIBlockElementAdd", "ResizeUploadedPhoto");
//AddEventHandler("iblock", "OnAfterIBlockElementUpdate", "ResizeUploadedPhoto");

function ResizeUploadedPhoto($arFields)
{
    CModule::IncludeModule('iblock');
    $IBLOCK_ID = 2; // ID инфоблока свойство которых нуждается в масштабировании
    $PROPERTY_CODE = "MORE_PHOTO";  // код свойства
    $imageMaxWidth = 640; // Максимальная ширина картинки
    $imageMaxHeight = 640; // Максимальная высота картинки
    // для начала убедимся, что изменяется элемент нужного нам инфоблока
    if ($arFields["IBLOCK_ID"] == $IBLOCK_ID) {
        $resizedFiles = $VALUES = $VALUES_OLD = [];
        $HLImagesOriginal = \Level44\HLWrapper::table(\Level44\Base::IMAGES_ORIGINAL_HL_TBL_NAME);

        if (!empty($arFields["DETAIL_PICTURE"]["name"])) {
            $elem = \Bitrix\Iblock\ElementTable::getList(
                [
                    "filter" => [
                        "IBLOCK_ID" => $arFields["IBLOCK_ID"],
                        "ID"        => $arFields["ID"]
                    ]
                ])->fetch();

            if ((int)$elem["DETAIL_PICTURE"] > 0) {
                $detailImagePath = \CFile::GetPath($elem["DETAIL_PICTURE"]);
                if ($detailImagePath) {
                    $imsize = getimagesize($_SERVER["DOCUMENT_ROOT"] . $detailImagePath); //Узнаём размер файла
                    // Если размер больше установленного максимума
                    if ($imsize[0] > $imageMaxWidth or $imsize[1] > $imageMaxHeight) {
                        // Уменьшаем размер картинки
                        $file = \CFile::ResizeImageGet($elem["DETAIL_PICTURE"], [
                            'width'  => $imageMaxWidth,
                            'height' => $imageMaxHeight
                        ], BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true);
                        $elemOb = new \CIBlockElement();

                        $hlResult = $HLImagesOriginal->add(
                            [
                                "UF_IMAGE" => \CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . $detailImagePath),
                            ]
                        );

                        if ($hlResult->isSuccess()) {
                            $resUpdate = $elemOb->update(
                                $arFields["ID"],
                                [
                                    "DETAIL_PICTURE" => \CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . $file["src"])
                                ]
                            );

                            if ($resUpdate) {
                                $updatedElem = \Bitrix\Iblock\ElementTable::getList(
                                    [
                                        "filter" => [
                                            "IBLOCK_ID" => $arFields["IBLOCK_ID"],
                                            "ID"        => $arFields["ID"]
                                        ],
                                        "select" => [
                                            "ID",
                                            "IBLOCK_ID",
                                            "DETAIL_PICTURE",
                                        ]
                                    ])->fetch();


                                if (!$HLImagesOriginal->getList(["filter" => ["ID" => $updatedElem["DETAIL_PICTURE"]]])->fetch()) {
                                    $HLImagesOriginal->update($hlResult->getId(), [
                                            "UF_RESIZED_IMAGE_ID" => $updatedElem["DETAIL_PICTURE"],
                                        ]
                                    );
                                } else {
                                    $HLImagesOriginal->delete($hlResult->getId());
                                }
                                \CFile::Delete($elem["DETAIL_PICTURE"]);
                            }
                        }
                    }
                }
            }
        }
        //Получаем свойство значение сво-ва $PROPERTY_CODE
        $res = \CIBlockElement::GetProperty($arFields["IBLOCK_ID"], $arFields["ID"], "sort", "asc", ["CODE" => $PROPERTY_CODE]);
        while ($ob = $res->GetNext()) {
            $file_path = \CFile::GetPath($ob['VALUE']); // Получаем путь к файлу
            if ($file_path) {
                $imsize = getimagesize($_SERVER["DOCUMENT_ROOT"] . $file_path); //Узнаём размер файла
                // Если размер больше установленного максимума
                if ($imsize[0] > $imageMaxWidth or $imsize[1] > $imageMaxHeight) {
                    // Уменьшаем размер картинки
                    $file = \CFile::ResizeImageGet($ob['VALUE'], [
                        'width'  => $imageMaxWidth,
                        'height' => $imageMaxHeight
                    ], BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true);
                    // добавляем в массив VALUES новую уменьшенную картинку
                    $resizedFileArray = \CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . $file["src"]);
                    $VALUES[] = $resizedFileArray;
                    $resFile = $HLImagesOriginal->add(
                        [
                            "UF_IMAGE" => \CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . $file_path),
                        ]
                    );
                    if ($resFile) {
                        $resizedFiles[$resizedFileArray["name"]] = $resFile->getId();
                    }
                } else {
                    // добавляем в массив VALUES старую картинку
                    $VALUES[] = \CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . $file_path);
                }
                // Собираем в массив ID старых файлов для их удаления (чтобы не занимали место)
                $VALUES_OLD[] = $ob['VALUE'];
            }
        }

        if (empty($resizedFiles)) {
            return true;
        }

        // Если в массиве есть информация о новых файлах
        if (count($VALUES) > 0) {
            $PROPERTY_VALUE = $VALUES;  // значение свойства
            // Установим новое значение для данного свойства данного элемента
            \CIBlockElement::SetPropertyValuesEx($arFields["ID"], $arFields["IBLOCK_ID"], [$PROPERTY_CODE => $PROPERTY_VALUE]);

            if (!empty($resizedFiles)) {
                $rsMorePhoto = \CIBlockElement::GetProperty($arFields["IBLOCK_ID"], $arFields["ID"], "sort", "asc", ["CODE" => "MORE_PHOTO"]);
                $arMorePhotos = [];
                while ($morePhoto = $rsMorePhoto->GetNext()) {
                    $arMorePhotos[] = $morePhoto["VALUE"];
                }
                if (!empty($arMorePhotos)) {
                    $savedFiles = \CFile::GetList([], ["@ID" => implode(",", $arMorePhotos)]);
                    while ($file = $savedFiles->GetNext()) {
                        if (array_key_exists($file["ORIGINAL_NAME"], $resizedFiles)) {
                            if (!$HLImagesOriginal->getList(["filter" => ["ID" => $file["ID"]]])->fetch()) {
                                $HLImagesOriginal->update($resizedFiles[$file["ORIGINAL_NAME"]], [
                                        "UF_RESIZED_IMAGE_ID" => $file["ID"],
                                    ]
                                );
                            } else {
                                $HLImagesOriginal->delete($resizedFiles[$file["ORIGINAL_NAME"]]);
                            }
                        }
                    }
                }
            }

            // Удаляем старые большие изображения
            foreach ($VALUES_OLD as $key => $val) {
                \CFile::Delete($val);
            }
        }
        unset($VALUES);
        unset($VALUES_OLD);
    }
}


function retailCrmBeforeOrderSave($order)
{
    $filename = $_SERVER['DOCUMENT_ROOT'].'/upload/logs/crm-'.date('Y-m-d').'.txt';
    $f = fopen($filename, 'a');
    fwrite($f, date('d.m.Y H:i:s')." -  order - ".print_r($order));
    fclose($f);

}

function retailCrmBeforeOrderSend($order, $arOrder)
{
    //TODO Refactor
    try {
        $bitrixOrder = \Bitrix\Sale\Order::load($arOrder["ID"]);
        [$deliveryId] = $bitrixOrder?->getDeliveryIdList();
        /** @var \Bitrix\Sale\BasketItem[] $basketItems */
        $basketItems = $bitrixOrder->getBasket()->getBasketItems();
        $propertyCollection = $bitrixOrder->getPropertyCollection();

        $getPropertyValue = function (PropertyValueCollectionBase $collection, string $code): string|null {
            $properties = $collection->getItemsByOrderPropertyCode($code);
            $property = empty($properties) ? null : current($properties);

            return $property?->getValue();
        };

        [$firstName, $lastName, $secondName] = [
            $getPropertyValue($propertyCollection, 'FIRST_NAME'),
            $getPropertyValue($propertyCollection, 'LAST_NAME'),
            $getPropertyValue($propertyCollection, 'SECOND_NAME'),
        ];

        if (!empty($firstName)) {
            $order['firstName'] = $firstName;
        }

        if (!empty($lastName)) {
            $order['lastName'] = $lastName;
        }

        if (!empty($secondName)) {
            $order['patronymic'] = $secondName;
        }

        if (in_array($deliveryId, CDeliverySDEK::getDeliveryId('pickup')) && empty($order["delivery"]["address"]["text"])) {
            $pickupAddress = current(
                array_filter($arOrder["PROPS"]["properties"], fn($item) => $item["CODE"] === 'ADDRESS_SDEK')
            );

            if ($pickupAddress["VALUE"][0]) {
                $order["delivery"]["address"]["text"] = $pickupAddress["VALUE"][0];
            }
        }

        if ($order["delivery"]['service']['code']=='dalli_courier')
        {
            $order["delivery"]['service']['code']='bitrix-31';
        }



        $offersSize = [];

        foreach ($basketItems as $basketItem) {
            /** @var \Bitrix\Sale\BasketPropertiesCollectionBase $propertyCollection */
            $propertyCollection = $basketItem->getPropertyCollection();
            $items = array_filter($propertyCollection->toArray(), fn($property) => $property['CODE'] === 'SIZE_REF');
            $offersSize[$basketItem->getProductId()] = empty($items) ?
                null : current(array_map(fn($item) => $item["VALUE"], $items));
        }

        if (is_array($order['items'])) {
            $order['items'] = array_map(function ($item) use ($offersSize) {
                if (!empty($item["offer"]['externalId']) && $offersSize[$item["offer"]['externalId']]) {
                    $item["properties"][] = [
                        'code'  => 'size',
                        'name'  => 'размер',
                        'value' => $offersSize[$item["offer"]['externalId']]
                    ];
                }

                return $item;
            }, $order['items']);
        }
    } catch (\Exception $exception) {
    }

    return $order;
}

function retailCrmAfterOrderSave($order)
{
    try {

        $bitrixOrder = \Bitrix\Sale\Order::load($order['externalId']);
        $propertyCollection = $bitrixOrder->getPropertyCollection();
        $changed = false;

        /** @var \Level44\Sale\PropertyValue $property */
        foreach ($propertyCollection as $property) {
            if (!empty($order['firstName']) && $property->getField('CODE') === 'FIRST_NAME') {
                $property->setValue($order['firstName']);
                $changed = true;
            }

            if (!empty($order['lastName']) && $property->getField('CODE') === 'LAST_NAME') {
                $property->setValue($order['lastName']);
                $changed = true;
            }

            if (!empty($order['patronymic']) && $property->getField('CODE') === 'SECOND_NAME') {
                $property->setValue($order['patronymic']);
                $changed = true;
            }
        }

        if ($changed) {
            $propertyCollection->save();
        }

    } catch (\Exception $e) {
    }
}

/**
 * Хук для обработки после сохранения клиента из RetailCRM
 * Вызывается модулем RetailCRM после успешной синхронизации клиента
 *
 * @param array $customer Данные клиента из RetailCRM
 * @return void
 */
function retailCrmAfterCustomerSave($customer)
{
    // Вызываем обработчик синхронизации лояльности
    \Level44\Event\RetailCrmLoyaltyHandlers::onAfterCustomerSave($customer);
}

class AcritBonusInOrderOpensourceIntegration
{
    public static function init(): void
    {
        $eventManager = Bitrix\Main\EventManager::getInstance();
        $eventManager->addEventHandler('sale', 'OnSaleOrderSaved',
            static function (Bitrix\Main\Event $event) {
                /** @var \Bitrix\Sale\Order $order */
                $order = $event->getParameter("ENTITY");
                $isNew = $event->getParameter("IS_NEW");

                if (\Bitrix\Main\Loader::includeModule('acrit.bonus') && $isNew) {
                    $params = [];
                    // if bonus-fields outside main order form-tag
                    if ((int)$_SESSION['BONUS_PAY_USER_VALUE'] > 0) {
                        $params['PAY_BONUS_ACCOUNT'] = 'Y';
                    }
                    \Acrit\Bonus\Core::OnSaleComponentOrderOneStepComplete($order->getId(), $order->getFieldValues(), $params);
                }
            }
        );
    }
}
AcritBonusInOrderOpensourceIntegration::init();



function getUserOrderSumm($userId)
{
    Bitrix\Main\Loader::includeModule('sale');

    if ($userId) {
        $totalPaid = 0;

        $orders = Bitrix\Sale\Order::getList([
            'filter' => [
                'USER_ID' => $userId,
                'PAYED' => 'Y',
                'CANCELED' => 'N'
            ],
            'select' => ['ID', 'PRICE']
        ]);

        while ($order = $orders->fetch()) {
            $totalPaid += $order['PRICE'];
        }

        return  $totalPaid;
    }
}
