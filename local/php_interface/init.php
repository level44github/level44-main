<?php

\CModule::AddAutoloadClasses(
    "",
    [
        "\Level44\Base"      => "/local/php_interface/lib/Base.php",
        "\Level44\HLWrapper" => "/local/php_interface/lib/HLWrapper.php",
        "\Level44\Content"   => "/local/php_interface/lib/Content.php",
        "\Level44\Product"   => "/local/php_interface/lib/Product.php",
    ]
);

if (\Level44\Base::isEnLang()) {
    global $MESS;

    $MESS['IPOLSDEK_DELIV_ERR_NOPVZ'] = "To place an order, please specify an SDEK pick-up point.";
    $MESS['IPOLSDEK_FRNT_CHOOSEPICKUP'] = "Select a pick-up point";
}

\Level44\Base::customRegistry();
\Level44\EventHandlers::register();

// События которые срабатывают при создании или изменении элемента инфоблока
AddEventHandler("iblock", "OnAfterIBlockElementAdd", "ResizeUploadedPhoto");
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", "ResizeUploadedPhoto");

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
                        $file = \CFile::ResizeImageGet($elem["DETAIL_PICTURE"], array(
                            'width'  => $imageMaxWidth,
                            'height' => $imageMaxHeight
                        ), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true);
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
        $res = \CIBlockElement::GetProperty($arFields["IBLOCK_ID"], $arFields["ID"], "sort", "asc", array("CODE" => $PROPERTY_CODE));
        while ($ob = $res->GetNext()) {
            $file_path = \CFile::GetPath($ob['VALUE']); // Получаем путь к файлу
            if ($file_path) {
                $imsize = getimagesize($_SERVER["DOCUMENT_ROOT"] . $file_path); //Узнаём размер файла
                // Если размер больше установленного максимума
                if ($imsize[0] > $imageMaxWidth or $imsize[1] > $imageMaxHeight) {
                    // Уменьшаем размер картинки
                    $file = \CFile::ResizeImageGet($ob['VALUE'], array(
                        'width'  => $imageMaxWidth,
                        'height' => $imageMaxHeight
                    ), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true);
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
            \CIBlockElement::SetPropertyValuesEx($arFields["ID"], $arFields["IBLOCK_ID"], array($PROPERTY_CODE => $PROPERTY_VALUE));

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

function retailCrmBeforeOrderSend($order, $arOrder)
{
    try {
        [$deliveryId] = \Bitrix\Sale\Order::load($arOrder["NUMBER"])?->getDeliveryIdList();

        if (in_array($deliveryId, CDeliverySDEK::getDeliveryId('pickup')) && empty($order["delivery"]["address"]["text"])) {
            $pickupAddress = current(
                array_filter($arOrder["PROPS"]["properties"], fn($item) => $item["CODE"] === 'ADDRESS_SDEK')
            );

            if ($pickupAddress["VALUE"][0]) {
                $order["delivery"]["address"]["text"] = $pickupAddress["VALUE"][0];
            }
        }
    } catch (\Exception $exception) {
    }

    return $order;
}