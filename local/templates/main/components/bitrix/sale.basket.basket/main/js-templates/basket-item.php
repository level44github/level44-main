<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

/**
 * @var array $mobileColumns
 * @var array $arParams
 * @var string $templateFolder
 */

$usePriceInAdditionalColumn = in_array('PRICE', $arParams['COLUMNS_LIST']) && $arParams['PRICE_DISPLAY_MODE'] === 'Y';
$useSumColumn = in_array('SUM', $arParams['COLUMNS_LIST']);
$useActionColumn = in_array('DELETE', $arParams['COLUMNS_LIST']);

$restoreColSpan = 2 + $usePriceInAdditionalColumn + $useSumColumn + $useActionColumn;

$positionClassMap = array(
    'left' => 'basket-item-label-left',
    'center' => 'basket-item-label-center',
    'right' => 'basket-item-label-right',
    'bottom' => 'basket-item-label-bottom',
    'middle' => 'basket-item-label-middle',
    'top' => 'basket-item-label-top'
);

$discountPositionClass = '';
if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y' && !empty($arParams['DISCOUNT_PERCENT_POSITION'])) {
    foreach (explode('-', $arParams['DISCOUNT_PERCENT_POSITION']) as $pos) {
        $discountPositionClass .= isset($positionClassMap[$pos]) ? ' ' . $positionClassMap[$pos] : '';
    }
}

$labelPositionClass = '';
if (!empty($arParams['LABEL_PROP_POSITION'])) {
    foreach (explode('-', $arParams['LABEL_PROP_POSITION']) as $pos) {
        $labelPositionClass .= isset($positionClassMap[$pos]) ? ' ' . $positionClassMap[$pos] : '';
    }
}
?>
<script id="basket-item-template" type="text/html">
    <div class="cart__item" id="basket-item-{{ID}}" data-entity="basket-item" data-id="{{ID}}">
        <a class="cart__image" href="{{DETAIL_PAGE_URL}}">
            <img class="img-fluid" src="{{IMAGE_URL}}" alt="">
        </a>
        <div class="cart__body">
            <div class="font-weight-bold d-lg-none"><span>{{{PRICE_FORMATED}}}</span>&middot;<span> $ 120</span></div>
            <a class="cart__link" href="{{DETAIL_PAGE_URL}}">{{NAME}}</a>
            <ul class="cart__list">
                {{#SELECT_PROP.COLOR_REF}}
                <li><?= Loc::getMessage("PROP_COLOR") ?>: {{SELECT_PROP.COLOR_REF.VALUE}}</li>
                {{/SELECT_PROP.COLOR_REF}}
                {{#SELECT_PROP.SIZE_REF}}
                <li><?= Loc::getMessage("PROP_SIZE") ?>: {{SELECT_PROP.SIZE_REF.VALUE}}</li>
                {{/SELECT_PROP.SIZE_REF}}
            </ul>
            <a class="cart__remove mt-3 d-none d-lg-block" href="#" data-entity="basket-item-delete"><?= Loc::getMessage("DELETE") ?></a>
        </div>
        <div class="cart__actions">
            <div class="stepper js-stepper" data-entity="basket-item-quantity-block">
                <button class="btn btn-link js-stepper__btn stepper__btn stepper__btn_down"
                        type="button"
                        data-entity="basket-item-quantity-minus"
                ></button>
                <input class="form-control stepper__input js-stepper__input"
                       type="number"
                       value="{{QUANTITY}}"
                       min="1"
                       data-value="{{QUANTITY}}" data-entity="basket-item-quantity-field"
                       id="basket-item-quantity-{{ID}}"
                >
                <button class="btn btn-link js-stepper__btn stepper__btn stepper__btn_up"
                        type="button"
                        data-entity="basket-item-quantity-plus"
                ></button>
            </div>
            <a class="cart__remove d-lg-none" href="#" data-entity="basket-item-delete"><?= Loc::getMessage("DELETE") ?></a>
        </div>
        <div class="d-none d-lg-block cart__price">{{{PRICE_FORMATED}}}</div>
    </div>
</script>
