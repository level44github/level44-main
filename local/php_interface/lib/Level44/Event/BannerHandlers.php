<?php

namespace Level44\Event;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Json;
use CIBlockProperty;
use Level44\Base;

class BannerHandlers extends HandlerBase
{
    public static function register()
    {
        static::addEventHandler('iblock', 'OnBeforeIBlockElementAdd');
        static::addEventHandler("iblock", "OnBeforeIBlockElementUpdate");
        static::addEventHandler("iblock", "OnBeforeIBlockElementUpdate");
        static::addEventHandler("main", "OnEpilog");


    }

    /**
     * @param $arFields
     * @return bool
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function OnBeforeIBlockElementAddHandler(&$arFields): bool
    {
        return static::iBlockElementSaveHandler($arFields);
    }

    /**
     * @param $arFields
     * @return bool
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function OnBeforeIBlockElementUpdateHandler(&$arFields): bool
    {
        return static::iBlockElementSaveHandler($arFields);
    }

    /**
     * @param $arFields
     * @return bool
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function iBlockElementSaveHandler(&$arFields): bool
    {
        if ($arFields["IBLOCK_ID"] !== Base::BANNER_SLIDES_IBLOCK_ID) {
            return true;
        }

        global $APPLICATION;

        if (empty($arFields['ID'])) {
            $count = ElementTable::getCount(['IBLOCK_ID' => Base::BANNER_SLIDES_IBLOCK_ID]);

            if ($count >= 5) {
                $APPLICATION->throwException("Максимальное количество слайдов: 5");
                return false;
            }
        }

        $properties = static::getProperties(Base::BANNER_SLIDES_IBLOCK_ID);

        $linkAddress = (string)static::getPropertyValue($arFields["PROPERTY_VALUES"][$properties["LINK_ADDRESS"]]);

        if (!empty($linkAddress)) {
            mb_substr($linkAddress, -1) === '/' ?: ($linkAddress .= '/');
            $linkAddress = ltrim($linkAddress, '/');
            $linkAddress = preg_replace('/\/{2,}/', '/', $linkAddress);

            static::setPropertyValue($arFields["PROPERTY_VALUES"][$properties["LINK_ADDRESS"]], $linkAddress);
        }

        $linkSection = static::getPropertyValue($arFields["PROPERTY_VALUES"][$properties["LINK_SECTION"]]);

        if (empty($linkSection) && empty($linkAddress)) {
            $APPLICATION->throwException("Необходимо выбрать раздел ссылки или указать адрес ссылки");
            return false;
        }

        $fileDesktop = (string)static::getPropertyValue($arFields["PROPERTY_VALUES"][$properties["FILE_DESKTOP"]]);
        $splitFile1 = (string)static::getPropertyValue($arFields["PROPERTY_VALUES"][$properties["SPLIT_FILE_1"]]);
        $splitFile2 = (string)static::getPropertyValue($arFields["PROPERTY_VALUES"][$properties["SPLIT_FILE_2"]]);

        if (!$fileDesktop && !$splitFile1 && !$splitFile2) {
            $APPLICATION->throwException("Необходимо загрузить Цельное изображение/видео (Desktop) или Раздельное изображение (Desktop)");
            return false;
        }

        if ($splitFile1 xor $splitFile2) {
            $APPLICATION->throwException("Необходимо загрузить две части раздельноого изображения");
            return false;
        }

        return true;
    }

    /**
     * @return true
     * @throws ArgumentException
     */
    public static function OnEpilogHandler()
    {
        global $APPLICATION;
        if ($APPLICATION->GetCurPage() !== "/bitrix/admin/iblock_element_edit.php") {
            return true;
        }

        if ((int)Context::getCurrent()->getRequest()->get("IBLOCK_ID") !== Base::BANNER_SLIDES_IBLOCK_ID) {
            return true;
        }

        $properties = static::getProperties();

        \CJSCore::Init(["jquery"]);
        ?>
        <script>
            window.bannerProperties = <?=Json::encode($properties)?>;

            $(document).ready(function () {
                if (!$('#tr_LINK_ADDRESS_NOTE').length) {
                    $(`.adm-detail-content`).find(`#tr_PROPERTY_${bannerProperties.LINK_ADDRESS}`).before(`<tr id="tr_LINK_ADDRESS_NOTE">
                        <td colspan="2" class="display: inline-block;">
                            <div class="adm-info-message-wrap adm-info-message-gray" style="text-align: center;">
                                <div class="adm-info-message">Если указан адрес ссылки, выбранный раздел будет проигнорирован</div>
                            </div>
                        </td>
                    </tr>`);
                }

                if (!$('#tr_FILE_DESKTOP_NOTE').length) {
                    $(`.adm-detail-content`).find(`#tr_PROPERTY_${bannerProperties.FILE_DESKTOP}`).after(`<tr id="tr_FILE_DESKTOP_NOTE">
                        <td colspan="2" class="display: inline-block;">
                            <div class="adm-info-message-wrap adm-info-message-gray" style="text-align: center;">
                                <div class="adm-info-message">Раздельные изображения будут отображены, только если не загружено цельное</div>
                            </div>
                        </td>
                    </tr>`);
                }
            });
        </script>
        <?
        return true;
    }
}
