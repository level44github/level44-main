<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die;
}
/**
 * 
 * @var CBitrixComponentTemplate $this
 * @var array $arResult
 * @var array $arParams
 */
$this->setFrameMode(true);
$oManager = \BXmaker\AuthUserPhone\Manager::getInstance();
\Bitrix\Main\UI\Extension::load('bxmaker.authuserphone.simple');
$jsNodeId = 'bxmaker-authuserphone-simple__' . $arParams['RAND_STRING'];
$jsVarMain = 'BXmakerAuthuserphoneSimple__' . $arParams['RAND_STRING'];
$jsVarParam = 'BXmakerAuthuserphoneSimpleParams__' . $arParams['RAND_STRING'];
if ($arParams['PHONE_MASK_PARAMS'] && $arParams['PHONE_MASK_PARAMS']['type'] === 'bitrix' && $arParams['PHONE_MASK_PARAMS']['onlySelected']) {
    echo \BXmaker\AuthUserPhone\Html\Helper::getStyleForBitrixPhoneCountryOnlySelected();
}
?>
    <script>
        BX.message({
            BXMAKER_AUTHUSERPHONE_SIMPLE_SMS_CODE_TITLE: '<?=GetMessageJS('BXMAKER_AUTHUSERPHONE_SIMPLE_SMS_CODE_TITLE')?>',
            BXMAKER_AUTHUSERPHONE_SIMPLE_SMS_CODE_TIMEOUT_TITLE: '<?=GetMessageJS('BXMAKER_AUTHUSERPHONE_SIMPLE_SMS_CODE_TIMEOUT_TITLE')?>',
            BXMAKER_AUTHUSERPHONE_SIMPLE_SMS_CODE_TIMEOUT_SUBTITLE: '<?=GetMessageJS('BXMAKER_AUTHUSERPHONE_SIMPLE_SMS_CODE_TIMEOUT_SUBTITLE')?>',
            BXMAKER_AUTHUSERPHONE_SIMPLE_SMS_CODE_NOTICE: '<?=GetMessageJS('BXMAKER_AUTHUSERPHONE_SIMPLE_SMS_CODE_NOTICE')?>',
            BXMAKER_AUTHUSERPHONE_SIMPLE_SMS_CODE_GET_NEW: '<?=GetMessageJS('BXMAKER_AUTHUSERPHONE_SIMPLE_SMS_CODE_GET_NEW')?>',
            BXMAKER_AUTHUSERPHONE_SIMPLE_SMS_CODE_CHANGE_PHONE: '<?=GetMessageJS('BXMAKER_AUTHUSERPHONE_SIMPLE_SMS_CODE_CHANGE_PHONE')?>',
            BXMAKER_AUTHUSERPHONE_SIMPLE_PHONE_PASSWORD_INPUT_PHONE: '<?=GetMessageJS('BXMAKER_AUTHUSERPHONE_SIMPLE_PHONE_PASSWORD_INPUT_PHONE')?>',
            BXMAKER_AUTHUSERPHONE_SIMPLE_PHONE_INVALID: '<?=GetMessageJS('BXMAKER_AUTHUSERPHONE_SIMPLE_PHONE_INVALID')?>',
            BXMAKER_AUTHUSERPHONE_SIMPLE_PHONE_INPUT_LABEL: '<?=GetMessageJS('BXMAKER_AUTHUSERPHONE_SIMPLE_PHONE_INPUT_LABEL')?>',
            BXMAKER_AUTHUSERPHONE_SIMPLE_PHONE_BUTTON_GET_CODE: '<?=GetMessageJS('BXMAKER_AUTHUSERPHONE_SIMPLE_PHONE_BUTTON_GET_CODE')?>',
            BXMAKER_AUTHUSERPHONE_SIMPLE_PHONE_BUTTON_CONFIRM: '<?=GetMessageJS('BXMAKER_AUTHUSERPHONE_SIMPLE_PHONE_BUTTON_CONFIRM')?>',
        });
    </script>
    <div id="<?php 
echo $jsNodeId;
?>">
        <div class="bxmaker-authuserphone-loader"></div>
    </div>

<?php 
$frame = $this->createFrame($jsNodeId . '-frame')->begin('');
$frame->setAnimation(true);
?>

    <script type="text/javascript" class="bxmaker-jsdata">
        <?php 
echo sprintf('var %s = %s;', $jsVarParam, \Bitrix\Main\Web\Json::encode($arResult['JS_PARAMS']));
?>

        (function(){
            function init() {
                var count = 0;
                var interval = setInterval(function () {
                    if (count++ > 30) {
                        clearInterval(interval);
                    }
                    if (!!document.getElementById('<?php 
echo $jsNodeId;
?>')) {
                        clearInterval(interval);
                        window.<?php 
echo $jsVarMain;
?> = new window.BXmaker.Authuserphone.Simple(<?php 
echo sprintf(" '%s', %s ", $jsNodeId, $jsVarParam);
?>);
                    }
                }, 100);
            }

            if (!!window.BXmaker && !!window.BXmaker.Authuserphone && !!window.BXmaker.Authuserphone.Simple) {
                init();
            } else {
                BX.loadExt('bxmaker.authuserphone.simple').then(function () {
                    init();
                });
            }
        })();

    </script>

<?php 
$frame->end();