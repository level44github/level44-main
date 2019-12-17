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

        <?endif?>

        var BXFormPosting = false;

        function submitForm(val) {
            if (BXFormPosting === true)
                return true;

            BXFormPosting = true;
            if (val !== 'Y') {
                BX('confirmorder').value = 'N';
            }

            var orderForm = BX('ORDER_FORM');
            BX.showWait();

            <?if(CSaleLocation::isLocationProEnabled()):?>
            BX.saleOrderAjax.cleanUp();
            <?endif?>

            BX.ajax.submit(orderForm, ajaxResult);

            return true;
        }

        function submitChangeLocation() {
            submitForm();
            $(".js-form__control[data-prop='ADDRESS']").val("")
        }

        function ajaxResult(res)
        {
            var orderForm = BX('ORDER_FORM');
            try
            {
                // if json came, it obviously a successfull order submit

                var json = JSON.parse(res);
                BX.closeWait();

                if (json.error)
                {
                    BXFormPosting = false;
                    return;
                }
                else if (json.redirect)
                {
                    window.top.location.href = json.redirect;
                }
            }
            catch (e)
            {
                // json parse failed, so it is a simple chunk of html

                BXFormPosting = false;
                if ($.isReady) {
                    var $obContent = $("<div></div>").append($(res));
                    $(".js-form_block").html($obContent.find(".js-form_block").html());
                    $(".js-basket_block").html($obContent.find(".js-basket_block").html());
                }

                <?if(CSaleLocation::isLocationProEnabled()):?>
                BX.saleOrderAjax.initDeferredControl();
                <?endif?>
            }

            BX.closeWait();
            BX.onCustomEvent(orderForm, 'onAjaxSuccess');
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
    <input type="hidden" name="json" value="Y">
    <div class="d-lg-none">
        <div class="form-group">
            <button class="btn btn-dark btn-block"
                    onclick="submitForm('Y'); return false;"
                    type="submit">Перейти к оформлению заказа</button>
        </div>
        <p class="text-muted">
            Нажимая кнопку «Оформить заказ», вы соглашаетесь с
            <a href="#">публичной офертой</a>
        </p>
    </div>

    </form>
    <? else:?>
    <script type="text/javascript">
        top.BX('confirmorder').value = 'Y';
        top.BX('profile_change').value = 'N';
    </script>
    <? endif;
    ?>
<?endif;?>