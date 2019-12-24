<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load("ui.fonts.ruble");

/**
 * @var array $arParams
 * @var array $arResult
 * @var string $templateFolder
 * @var string $templateName
 * @var CMain $APPLICATION
 * @var CBitrixBasketComponent $component
 * @var CBitrixComponentTemplate $this
 * @var array $giftParameters
 */

\CJSCore::Init(array('fx', 'popup', 'ajax'));

$this->addExternalJs($templateFolder . '/js/mustache.js');
$this->addExternalJs($templateFolder . '/js/action-pool.js');
$this->addExternalJs($templateFolder . '/js/filter.js');
$this->addExternalJs($templateFolder . '/js/component.js');


$jsTemplates = new Main\IO\Directory(Main\Application::getDocumentRoot() . $templateFolder . '/js-templates');
/** @var Main\IO\File $jsTemplate */
foreach ($jsTemplates->getChildren() as $jsTemplate) {
    include($jsTemplate->getPath());
}


if (empty($arResult['ERROR_MESSAGE'])):?>
    <div id="basket-root" class="row bx-basket">
        <div class="col-lg-8 mb-4">
            <h1 class="page__title cart__page-title">Корзина</h1>
            <div class="cart__items" id="basket-item-table"></div>
        </div>
        <div class="col-lg-4">
            <h3 class="aside__title">Итого</h3>
            <div class="card">
                <div class="card-body" data-entity="basket-total-block"></div>
            </div>
        </div>
    </div>
    <?
    if (!empty($arResult['CURRENCIES']) && Main\Loader::includeModule('currency')) {
        CJSCore::Init('currency');

        ?>
        <script>
            BX.Currency.setCurrencies(<?=CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true)?>);
        </script>
        <?
    }

    $signer = new \Bitrix\Main\Security\Sign\Signer;
    $signedTemplate = $signer->sign($templateName, 'sale.basket.basket');
    $signedParams = $signer->sign(base64_encode(serialize($arParams)), 'sale.basket.basket');
    $messages = Loc::loadLanguageFile(__FILE__);
    ?>
    <script>
        BX.message(<?=CUtil::PhpToJSObject($messages)?>);
        BX.Sale.BasketComponent.init({
            result: <?=CUtil::PhpToJSObject($arResult, false, false, true)?>,
            params: <?=CUtil::PhpToJSObject($arParams)?>,
            template: '<?=CUtil::JSEscape($signedTemplate)?>',
            signedParamsString: '<?=CUtil::JSEscape($signedParams)?>',
            siteId: '<?=CUtil::JSEscape($component->getSiteId())?>',
            siteTemplateId: '<?=CUtil::JSEscape($component->getSiteTemplateId())?>',
            templateFolder: '<?=CUtil::JSEscape($templateFolder)?>'
        });
        var basketJSParams = BX.Sale.BasketComponent.params
    </script>
<? elseif ($arResult['EMPTY_BASKET']): ?>
    <? include(Main\Application::getDocumentRoot() . $templateFolder . '/empty.php'); ?>
<? else: ?>
    <? ShowError($arResult['ERROR_MESSAGE']); ?>
<? endif; ?>
