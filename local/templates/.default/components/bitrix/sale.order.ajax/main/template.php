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

$this->addExternalJs($templateFolder . '/order_ajax.js');

use Bitrix\Main\Localization\Loc;

CJSCore::Init(array('fx', 'popup', 'window', 'ajax'));
?>

	<?
if($arResult["USER_VALS"]["CONFIRM_ORDER"] == "Y" || $arResult["NEED_REDIRECT"] == "Y"):?>

    <?if(strlen($arResult["REDIRECT_URL"]) === 0):?>
        <?include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/confirm.php");?>
    <?endif;?>

<?else:?>
    <?php
    $signer = new \Bitrix\Main\Security\Sign\Signer();
    $signedParams = $signer->sign(base64_encode(serialize($arParams)), 'sale.order.ajax');
    $messages = Loc::loadLanguageFile(__FILE__);
    ?>
    <script>
        BX.message(<?=CUtil::PhpToJSObject($messages)?>);
        BX.Sale.OrderAjaxComponent.init({
            result: <?=CUtil::PhpToJSObject($arResult['JS_DATA'])?>,
            locations: <?=CUtil::PhpToJSObject($arResult['LOCATIONS'])?>,
            params: <?=CUtil::PhpToJSObject($arParams)?>,
            signedParamsString: '<?=CUtil::JSEscape($signedParams)?>',
            siteID: '<?=CUtil::JSEscape($component->getSiteId())?>',
            ajaxUrl: '<?=CUtil::JSEscape($component->getPath().'/ajax.php')?>',
            templateFolder: '<?=CUtil::JSEscape($templateFolder)?>',
            propertyValidation: true,
            showWarnings: true,
            pickUpMap: {
                defaultMapPosition: {
                    lat: 55.76,
                    lon: 37.64,
                    zoom: 7
                },
                secureGeoLocation: false,
                geoLocationMaxTime: 5000,
                minToShowNearestBlock: 3,
                nearestPickUpsToShow: 3
            },
            propertyMap: {
                defaultMapPosition: {
                    lat: 55.76,
                    lon: 37.64,
                    zoom: 7
                }
            },
            orderBlockId: 'bx-soa-order',
            authBlockId: 'bx-soa-auth',
            basketBlockId: 'bx-soa-basket',
            regionBlockId: 'bx-soa-region',
            paySystemBlockId: 'bx-soa-paysystem',
            deliveryBlockId: 'bx-soa-delivery',
            pickUpBlockId: 'bx-soa-pickup',
            propsBlockId: 'bx-soa-properties',
            totalBlockId: 'bx-soa-total'
        });
    </script>
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

       <? if (\Bitrix\Main\Loader::includeModule('acrit.bonus')) {
        // формирует данные для оплаты, заполняется массив $arResult['BONUSPAY']
        \Acrit\Bonus\Profile::runPayProfiles($arResult);


           $user = new CUser;
           $rsUser = CUser::GetByID($USER->GetID());
           $arUser = $rsUser->Fetch();

           $KBONUS_ADD=0;

           if ($arUser['UF_LOYALTY_LEVEL_ID_INTARO']==9)
           {
               $KBONUS_ADD=round($arResult["ORDER_TOTAL_PRICE_NEW"]/100*3);
           }

           if ($arUser['UF_LOYALTY_LEVEL_ID_INTARO']==13)
           {
               $KBONUS_ADD=round($arResult["ORDER_TOTAL_PRICE_NEW"]/100*5);
           }


        ?>

        <?if (($USER->IsAuthorized()) and ($arUser['UF_LP_ID_INTARO']!='')){ ?>
        <div class="card" style="padding:1rem 2rem">
            <div>Баллы:</div>
            <?if ($arResult['BONUSPAY']['CURRENT_BONUS_BUDGET_FORMATED']!=''){?>
            <div class="bonus-order-wrap">
                <div class="CURRENT_BONUS_BUDGET">
                    У вас <?=$arResult['BONUSPAY']['CURRENT_BONUS_BUDGET_FORMATED']?>
                </div>
                <div class="bonus-line"></div>
                <input type="hidden" name="PAY_BONUS_ACCOUNT" value="Y">
                <input type="hidden" name="save" value="y" class="send_open_source_order_flag">
                <input type="number" onchange="maxLengthCheck(this)" class="bonus-input" placeholder="Списать"  name="BONUS_CNT" id="BONUS_CNT"  min="0" pattern="\d+(\.\d+)?" max="<?=$arResult['BONUSPAY']['MAXPAY']?>">
                <!--<input type="button" class="bonus-input" value="Списать"  onclick="AcritBonusPayBonusBtn()">-->
            </div>
            <?}?>
             <div class="bonus-order-wrap">
                 <div class="CURRENT_BONUS_BUDGET">
                     <?=$KBONUS_ADD?> баллов начислим
                 </div>
                <?if ((int)$arResult['BONUSPAY']['USER_VALUE']!=0){?>
                 <div class="CURRENT_BONUS_BUDGET">
                     <?=$arResult['BONUSPAY']['USER_VALUE']?> баллов спишем
                 </div>
                <?}?>

             </div>
        </div>
       <?}?>
       <?}?>
        <div class="d-none d-lg-block js-submit-block">
            <div class="privacy-checkboxes">
                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" id="subscribe" name="subscribe"
                            <?= $arResult['SUBSCRIBE_CHECKED'] ? 'checked' : '' ?>
                               value="Y">
                        <span><?= Loc::getMessage('CHECKOUT_NEWSLETTER_CONSENT', ['#SITE_DIR#' => SITE_DIR]) ?></span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" id="policy" name="policy" value="Y">
                        <span><?= Loc::getMessage('CHECKOUT_PRIVACY_CONSENT', ['#SITE_DIR#' => SITE_DIR]) ?></span>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <button class="btn btn-dark btn__fix-width" id="submit-button"
                        onclick="submitForm('Y'); return false;"
                    <? //Становится активным, если отметить согласие ?>
                        disabled
                        type="submit"><?= Loc::getMessage("CHECKOUT") ?></button>
            </div>
        </div>
        <input type="hidden" name="out_russia">
    </div>
    <div class="col-lg-4 js-basket_block">
        <?
        include($_SERVER["DOCUMENT_ROOT"] . $templateFolder . "/summary.php");
        ?>
        <div class="d-lg-none js-submit-block">
            <div class="privacy-checkboxes">
                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" id="subscribe-mobile" name="subscribe"
                            <?= $arResult['SUBSCRIBE_CHECKED'] ? 'checked' : '' ?>
                               value="Y">
                        <span><?= Loc::getMessage('CHECKOUT_NEWSLETTER_CONSENT', ['#SITE_DIR#' => SITE_DIR]) ?></span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" id="policy-mobile" name="policy" value="Y">
                        <span><?= Loc::getMessage('CHECKOUT_PRIVACY_CONSENT', ['#SITE_DIR#' => SITE_DIR]) ?></span>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <button class="btn btn-dark btn-block"
                        onclick="submitForm('Y'); return false;"
                    <? //Становится активным, если отметить согласие ?>
                        disabled
                        type="submit"><?= Loc::getMessage("CHECKOUT") ?></button>
            </div>
        </div>
    </div>
        <? if ($_POST["is_ajax_post"] !== "Y"): ?>
    <input type="hidden" name="confirmorder" id="confirmorder" value="Y">
    <input type="hidden" name="profile_change" id="profile_change" value="N">
    <input type="hidden" name="is_ajax_post" id="is_ajax_post" value="Y">
        <? if ($arResult["ORDER_PROP_ADDRESS"]): ?>
            <input type="hidden"
                   name="<?= $arResult["ORDER_PROP_ADDRESS"]["FIELD_NAME"] ?>"
                   data-prop="<?= $arResult["ORDER_PROP_ADDRESS"]["CODE"] ?>"
                   data-address-hidden
                   id="<?= $arResult["ORDER_PROP_ADDRESS"]["FIELD_NAME"] ?>-input"
                   value="">
        <? endif; ?>
    <input type="hidden" name="json" value="Y">
    </form>
    <? else:?>
    <script type="text/javascript">
        var addressPropName = '<?=(string)$arResult["ORDER_PROP_ADDRESS"]["FIELD_NAME"]?>';
        var addressPropCode = '<?=(string)$arResult["ORDER_PROP_ADDRESS"]["CODE"]?>';
        top.BX('confirmorder').value = 'Y';
        top.BX('profile_change').value = 'N';

        if (addressPropName) {
            top.BX.saleOrderAjax.address.fieldName = addressPropName;
            top.document.querySelector('[data-address-hidden]')?.remove()
            var input = top.document.createElement("input");
            input.type = "hidden";
            input.name = addressPropName;
            input.dataset.prop = addressPropCode;
            input.dataset.addressHidden = ''
            input.id = `${addressPropName}-input`
            input.value = ''
            top.document.getElementById('ORDER_FORM')?.append(input);
        }
    </script
    <? endif;
    ?>
<?endif;?>


<script>
    function maxLengthCheck(object)
    {
        if (parseInt(object.value) > parseInt(object.max)) object.value = object.max

        submitForm();
    }
</script>


<script>
    function AcritBonusPayBonusBtn() {

    }
</script>
