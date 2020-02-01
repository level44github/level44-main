<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

use Bitrix\Main\Localization\Loc;

CJSCore::init(array('popup', 'ajax'));

$this->setFrameMode(true);

$landingId = null;
if (is_callable(["LandingPubComponent", "getMainInstance"]))
{
	$instance = \LandingPubComponent::getMainInstance();
	$landingId = $instance["SITE_ID"];
}

$strMainId = $this->getEditAreaId($arResult['PRODUCT_ID']);
$jsObject = 'ob'.preg_replace("/[^a-zA-Z0-9_]/", "x", $strMainId);
$paramsForJs = array(
	'buttonId' => $arResult['BUTTON_ID'],
	'jsObject' => $jsObject,
	'alreadySubscribed' => $arResult['ALREADY_SUBSCRIBED'],
	'listIdAlreadySubscribed' => (!empty($_SESSION['SUBSCRIBE_PRODUCT']['LIST_PRODUCT_ID']) ?
		$_SESSION['SUBSCRIBE_PRODUCT']['LIST_PRODUCT_ID'] : []),
	'productId' => $arResult['PRODUCT_ID'],
	'buttonClass' => htmlspecialcharsbx($arResult['BUTTON_CLASS']),
	'urlListSubscriptions' => '/',
	'landingId' => ($landingId ? $landingId : 0)
);

$showSubscribe = true;

/* Compatibility with the sale subscribe option */
$saleNotifyOption = Bitrix\Main\Config\Option::get('sale', 'subscribe_prod');
if(strlen($saleNotifyOption) > 0)
	$saleNotifyOption = unserialize($saleNotifyOption);
$saleNotifyOption = is_array($saleNotifyOption) ? $saleNotifyOption : array();
foreach($saleNotifyOption as $siteId => $data)
{
	if($siteId == SITE_ID && $data['use'] != 'Y')
		$showSubscribe = false;
}
$templateData = $paramsForJs;
$templateData['showSubscribe'] = $showSubscribe;

$subscribeBtnName = !empty($arParams['MESS_BTN_SUBSCRIBE']) ? $arParams['MESS_BTN_SUBSCRIBE'] : Loc::getMessage('CPST_SUBSCRIBE_BUTTON_NAME');

if($showSubscribe):?>
    <button class="btn btn-block mb-4 btn-outline-dark <?= htmlspecialcharsbx($arResult['BUTTON_CLASS']) ?>"
            type="button"
            id="<?= htmlspecialcharsbx($arResult['BUTTON_ID']) ?>"
            data-item="<?= htmlspecialcharsbx($arResult['PRODUCT_ID']) ?>"
            style="<?= ($arResult['DEFAULT_DISPLAY'] ? '' : 'display: none;') ?>">
        <?= $subscribeBtnName ?>
    </button>
    <button type="button"
            class="modal-toggle"
            data-toggle="modal"
            data-target="#subscribe-modal"
            style="display:none;"
    ></button>
    <div class="js-subscribe-modal modal fade" id="subscribe-modal" tabindex="-1" role="dialog"
         aria-hidden="true">
        <div class="js-subscribe-form modal-dialog modal-dialog-centered product-subscribe__dialog"
             style="display: none"
             role="document"
        >
            <div class="modal-content">
                <button class="close product-subscribe__close" type="button" data-dismiss="modal"
                        aria-label="Закрыть">
                    <svg class="icon icon-close ">
                        <use xlink:href="#close"></use>
                    </svg>
                </button>
                <div class="product-subscribe__header">
                    <h5 class="product-subscribe__title"><?= Loc::getMessage("TITLE_POPUP_SUBSCRIBED") ?></h5>
                </div>
                <div class="js-subscribe-form-body modal-body product-subscribe__body">
                    <div class="text-center px-lg-1 mb-3"><?= Loc::getMessage("DESC_POPUP_SUBSCRIBED") ?></div>
                </div>
            </div>
        </div>

        <div class="js-subscribe-suc modal-dialog modal-dialog-centered product-subscribe__dialog"
             style="display: none"
             role="document"
        >
            <div class="modal-content">
                <button class="close product-subscribe__close" type="button" data-dismiss="modal"
                        aria-label="Закрыть">
                    <svg class="icon icon-close ">
                        <use xlink:href="#close"></use>
                    </svg>
                </button>
                <div class="product-subscribe__header">
                    <h5 class="product-subscribe__title"><?= Loc::getMessage("TITLE_POPUP_SUBSCRIBED") ?></h5>
                </div>
                <div class="modal-body product-subscribe__body">
                    <div class="js-text text-center px-lg-1 mb-3"></div>


                    <button class="btn btn-dark btn-block" type="submit"><?=Loc::getMessage("CPST_SUBSCRIBE_BUTTON_CLOSE")?></button>

                </div>
            </div>
        </div>
    </div>
	<input type="hidden" id="<?=htmlspecialcharsbx($arResult['BUTTON_ID'])?>_hidden">

	<script type="text/javascript">
		BX.message({
			CPST_SUBSCRIBE_POPUP_TITLE: '<?=GetMessageJS('CPST_SUBSCRIBE_POPUP_TITLE');?>',
			CPST_SUBSCRIBE_BUTTON_NAME: '<?=$subscribeBtnName?>',
			CPST_SUBSCRIBE_BUTTON_CLOSE: '<?=GetMessageJS('CPST_SUBSCRIBE_BUTTON_CLOSE');?>',
			CPST_SUBSCRIBE_MANY_CONTACT_NOTIFY: '<?=GetMessageJS('CPST_SUBSCRIBE_MANY_CONTACT_NOTIFY');?>',
			CPST_SUBSCRIBE_LABLE_CONTACT_INPUT: '<?=GetMessageJS('CPST_SUBSCRIBE_LABLE_CONTACT_INPUT');?>',
			CPST_SUBSCRIBE_VALIDATE_UNKNOW_ERROR: '<?=GetMessageJS('CPST_SUBSCRIBE_VALIDATE_UNKNOW_ERROR');?>',
			CPST_SUBSCRIBE_VALIDATE_ERROR_EMPTY_FIELD: '<?=GetMessageJS('CPST_SUBSCRIBE_VALIDATE_ERROR_EMPTY_FIELD');?>',
			CPST_SUBSCRIBE_VALIDATE_ERROR: '<?=GetMessageJS('CPST_SUBSCRIBE_VALIDATE_ERROR');?>',
			CPST_SUBSCRIBE_CAPTCHA_TITLE: '<?=GetMessageJS('CPST_SUBSCRIBE_CAPTCHA_TITLE');?>',
			CPST_STATUS_SUCCESS: '<?=GetMessageJS('CPST_STATUS_SUCCESS');?>',
			CPST_STATUS_ERROR: '<?=GetMessageJS('CPST_STATUS_ERROR');?>',
			CPST_ENTER_WORD_PICTURE: '<?=GetMessageJS('CPST_ENTER_WORD_PICTURE');?>',
			CPST_TITLE_ALREADY_SUBSCRIBED: '<?=GetMessageJS('CPST_TITLE_ALREADY_SUBSCRIBED');?>',
			CPST_POPUP_SUBSCRIBED_TITLE: '<?=GetMessageJS('CPST_POPUP_SUBSCRIBED_TITLE');?>',
			CPST_POPUP_SUBSCRIBED_TEXT: '<?=GetMessageJS('CPST_POPUP_SUBSCRIBED_TEXT');?>'
		});

		var <?=$jsObject?> = new JCCatalogProductSubscribe(<?=CUtil::phpToJSObject($paramsForJs, false, true)?>);
	</script>
<?endif;
