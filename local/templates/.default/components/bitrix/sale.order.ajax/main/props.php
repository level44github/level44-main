<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
include($_SERVER["DOCUMENT_ROOT"] . $templateFolder . "/props_format.php");
use Bitrix\Main\Localization\Loc;
?>

<? if (!empty($arResult["ORDER_PROP"]["USER_PROPS_Y"])): ?>
    <fieldset class="fieldset">
        <legend><?= Loc::getMessage("CONTACT") ?></legend>
        <? if (!empty($arResult["ORDER_PROP"]["USER_PROPS_Y"]["COLUMNS"])): ?>
            <? foreach ($arResult["ORDER_PROP"]["USER_PROPS_Y"]["COLUMNS"] as $row): ?>
                <div class="row">
                    <? foreach ($row as $col): ?>
                        <div class="col-lg-6">
                            <? PrintPropsForm($col, $arParams["TEMPLATE_LOCATION"]) ?>
                        </div>
                    <? endforeach; ?>
                </div>
            <? endforeach; ?>
        <? endif; ?>
        <? foreach ($arResult["ORDER_PROP"]["USER_PROPS_Y"]["FULLS"] as $row): ?>
            <? PrintPropsForm($row, $arParams["TEMPLATE_LOCATION"]) ?>
        <? endforeach; ?>
    </fieldset>
<? endif; ?>

<? if (!CSaleLocation::isLocationProEnabled()): ?>
    <div style="display:none;">

        <? $APPLICATION->IncludeComponent(
            "bitrix:sale.ajax.locations",
            $arParams["TEMPLATE_LOCATION"],
            array(
                "AJAX_CALL" => "N",
                "COUNTRY_INPUT_NAME" => "COUNTRY_tmp",
                "REGION_INPUT_NAME" => "REGION_tmp",
                "CITY_INPUT_NAME" => "tmp",
                "CITY_OUT_LOCATION" => "Y",
                "LOCATION_VALUE" => "",
                "ONCITYCHANGE" => "submitForm()",
            ),
            null,
            array('HIDE_ICONS' => 'Y')
        ); ?>

    </div>
<? endif ?>
