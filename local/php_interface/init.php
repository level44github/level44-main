<?php

\CModule::AddAutoloadClasses(
    "",
    [
        "\Level44\Base" => "/local/php_interface/lib/Base.php",
    ]
);

\Level44\Base::customRegistry();
\Level44\EventHandlers::register();

AddEventHandler("iblock", "OnAfterIBlockElementAdd", Array("MorePhotoResizeClass", "OnAfterIBlockElement"));
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", Array("MorePhotoResizeClass", "OnAfterIBlockElement"));

class MorePhotoResizeClass
{
    function Clear()
    {
        $_WFILE = glob($_SERVER['DOCUMENT_ROOT']."/upload/tmp/*");
        foreach($_WFILE as $_file)
            unlink($_file);
        return true;
    }

    function OnAfterIBlockElement(&$arFields)
    {
        global $APPLICATION, $USER;

        $PROPERTY_CODE = "MORE_PHOTO"; // код свойства с которым работаем

        $imageMaxWidth = 900; // Максимальная ширина уменьшенной картинки
        $imageMaxHeight = 1600; // Максимальная высота уменьшенной картинки

        // Находим свойство
        $dbRes = CIBlockElement::GetProperty($arFields["IBLOCK_ID"], $arFields["ID"], "sort", "asc", array("CODE" => $PROPERTY_CODE));
        while ($arMorePhoto = $dbRes->GetNext(true, false))
        {
            if ($arMorePhoto["PROPERTY_TYPE"] == "F" && $arMorePhoto["MULTIPLE"] == "Y")
            {
                // находим подробные сведения о файле
                $arFile = CFile::GetFileArray($arMorePhoto["VALUE"]);

                // проверяем, что файл является картинкой
                if (!CFile::IsImage($arFile["FILE_NAME"]))
                {
                    continue;
                }

                // Если размер больше допустимого
                if ($arFile["WIDTH"] > $imageMaxWidth || $arFile["HEIGHT"] > $imageMaxHeight)
                {
                    // Временная картинка
                    $tmpFilePath = $_SERVER['DOCUMENT_ROOT']."/upload/tmp/".$arFile["FILE_NAME"];

                    // Уменьшаем картинку
                    $resizeRez = CFile::ResizeImageFile( // уменьшение картинки для превью
                        $source = $_SERVER['DOCUMENT_ROOT'].$arFile["SRC"],
                        $dest = $tmpFilePath,
                        array(
                            'width' => $imageMaxWidth,
                            'height' => $imageMaxHeight,
                        ),
                        $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL, // метод ресайза
                        $jpgQuality = 95

               );

               // Записываем изменение в свойство
               if ($resizeRez)
               {
                   $arNewFile = CFile::MakeFileArray($tmpFilePath);

                   CIBlockElement::SetPropertyValueCode($arFields["ID"], $PROPERTY_CODE,
                       array($arMorePhoto["PROPERTY_VALUE_ID"] => array(
                           "VALUE" => $arNewFile,
                           "DESCRIPTION"=> $arMorePhoto["DESCRIPTION"],
                       ))
                   );
                   // Стираем временные файлы
                   MorePhotoResizeClass::Clear();
               }
            }
            }
        }
    }
}
