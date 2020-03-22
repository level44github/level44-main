<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if($USER->IsAuthorized() || $arParams["ALLOW_AUTO_REGISTER"] == "Y")
{
	if($arResult["USER_VALS"]["CONFIRM_ORDER"] == "Y" || $arResult["NEED_REDIRECT"] == "Y")
	{
		if(strlen($arResult["REDIRECT_URL"]) > 0)
		{
			$APPLICATION->RestartBuffer();
			?>
			<script type="text/javascript">
				window.top.location.href='<?=CUtil::JSEscape($arResult["REDIRECT_URL"])?>';
			</script>
			<?
			die();
		}

	}
}

use Bitrix\Main\Localization\Loc;

CJSCore::Init(array('fx', 'popup', 'window', 'ajax'));
?>

	<?
if($arResult["USER_VALS"]["CONFIRM_ORDER"] == "Y" || $arResult["NEED_REDIRECT"] == "Y"):?>

    <?if(strlen($arResult["REDIRECT_URL"]) === 0):?>
        <?include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/confirm.php");?>
    <?endif;?>

<?else:?>
    <script type="text/javascript">

        <?if(CSaleLocation::isLocationProEnabled()):?>

        <?
        // spike: for children of cities we place this prompt
        $city = \Bitrix\Sale\Location\TypeTable::getList(
            [
                'filter' => [
                    '=CODE' => 'CITY'
                ],
                'select' => [
                    'ID'
                ]
            ]
        )->fetch();
        ?>

        BX.saleOrderAjax.init(<?=CUtil::PhpToJSObject(
            [
                'source' => $this->__component->getPath() . '/get.php',
                'cityTypeId' => intval($city['ID']),
                'messages' => [
                    'otherLocation' => '--- ' . GetMessage('SOA_OTHER_LOCATION'),
                    'moreInfoLocation' => '--- ' . GetMessage('SOA_NOT_SELECTED_ALT'),
                    // spike: for children of cities we place this prompt
                    'notFoundPrompt' => '<div class="-bx-popup-special-prompt">' . GetMessage('SOA_LOCATION_NOT_FOUND') . '.<br />' . GetMessage('SOA_LOCATION_NOT_FOUND_PROMPT',
                            array(
                                '#ANCHOR#' => '<a href="javascript:void(0)" class="-bx-popup-set-mode-add-loc">',
                                '#ANCHOR_END#' => '</a>'
                            )) . '</div>'
                ]
            ]
        )?>);

        <?else:?>
        BX.saleOrderAjax.init({});
        <?endif?>

        BX.saleOrderAjax.BXFormPosting = false;
        <?if(CSaleLocation::isLocationProEnabled()):?>
        BX.saleOrderAjax.isLocationProEnabled = true;
        BX.saleOrderAjax.propAddressFieldName = '<?=(string)$arResult["ORDER_PROP_ADDRESS"]["FIELD_NAME"]?>';
        <?endif;?>

        function submitForm(val) {
            return BX.saleOrderAjax.submitForm(val);
        }

        function submitChangeLocation() {
            submitForm();
            $(".js-form__control[data-prop='ADDRESS']").val("")
        }

        function SetContact(profileId)
        {
            BX("profile_change").value = "Y";
            submitForm();
        }
    </script>
    <?if($_POST["is_ajax_post"] != "Y")
{
    ?><form action="<?=$APPLICATION->GetCurPage();?>" class="row"
            method="POST" name="ORDER_FORM"
            id="ORDER_FORM" enctype="multipart/form-data"
>
    <?=bitrix_sessid_post()?>
    <?
    }
    else
    {
        $APPLICATION->RestartBuffer();
    }?>
    <div class="col-lg-8 js-form_block">
        <div class="checkout-loading-overlay"></div>
        <h1 class="page__title"><?= Loc::getMessage("CHECKOUT_TITLE") ?></h1>
        <?

        if($_REQUEST['PERMANENT_MODE_STEPS'] == 1)
        {
            ?>
            <input type="hidden" name="PERMANENT_MODE_STEPS" value="1" />
            <?
        }

        if(!empty($arResult["ERROR"]) && $arResult["USER_VALS"]["FINAL_STEP"] == "Y")
        {
            foreach($arResult["ERROR"] as $v)
                echo ShowError($v);
            ?>
            <script type="text/javascript">
                top.BX.scrollToNode(top.BX('ORDER_FORM'));
            </script>
            <?
        }?>
        <?
        include($_SERVER["DOCUMENT_ROOT"] . $templateFolder . "/props.php");
        include($_SERVER["DOCUMENT_ROOT"] . $templateFolder . "/delivery.php");
        include($_SERVER["DOCUMENT_ROOT"] . $templateFolder . "/paysystem.php");
        ?>
        <div class="d-none d-lg-block">
            <div class="form-group">
                <button class="btn btn-dark btn__fix-width"
                        onclick="submitForm('Y'); return false;"
                        type="submit"><?= Loc::getMessage("CHECKOUT") ?></button>
            </div>
            <p class="text-muted"><?= Loc::getMessage("OFERTA_MESS1") ?><a href="<?= SITE_DIR ?>about/offer/">
                <?= Loc::getMessage("OFERTA") ?>
                </a>
            </p>
        </div>
    </div>
    <div class="col-lg-4 js-basket_block">
        <?
        include($_SERVER["DOCUMENT_ROOT"] . $templateFolder . "/summary.php");
        ?>

    </div>
        <? if ($_POST["is_ajax_post"] !== "Y"): ?>
    <input type="hidden" name="confirmorder" id="confirmorder" value="Y">
    <input type="hidden" name="profile_change" id="profile_change" value="N">
    <input type="hidden" name="is_ajax_post" id="is_ajax_post" value="Y">
    <input type="hidden"
           name="<?= $arResult["ORDER_PROP_ADDRESS"]["FIELD_NAME"] ?>"
           data-prop="<?= $arResult["ORDER_PROP_ADDRESS"]["CODE"] ?>"
           id="<?= $arResult["ORDER_PROP_ADDRESS"]["FIELD_NAME"] ?>-input"
           value="">
    <input type="hidden" name="json" value="Y">
    </form>
    <? else:?>
    <script type="text/javascript">
        top.BX('confirmorder').value = 'Y';
        top.BX('profile_change').value = 'N';
    </script>
    <? endif;
    ?>
<?endif;?>
