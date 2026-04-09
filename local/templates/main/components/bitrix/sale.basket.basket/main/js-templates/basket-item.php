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
            {{#SHOW_THREE_PRICES}}
            <!-- Мобильная версия: 3 цены -->
            <!-- 1. Зачеркнутая цена (без скидки) -->
            <div class="font-weight-bold d-lg-none d-dt-none cart__price-crossed">
                <span style="text-decoration: line-through; color: #999;">{{{ORIGINAL_PRICE_FORMATED}}}</span>
            </div>
            {{#ADDITIONAL_DISCOUNT_PERCENT}}
            <!-- 2. Промежуточная зачеркнутая цена (после первой скидки) -->
            <div class="font-weight-bold d-lg-none d-dt-none cart__price-crossed">
                <span style="text-decoration: line-through; color: #999;">{{{PRICE_BEFORE_ADDITIONAL_DISCOUNT_FORMATED}}}</span>
            </div>
            {{/ADDITIONAL_DISCOUNT_PERCENT}}
            <!-- 3. Основная цена (после дополнительной скидки, если есть, или после первой скидки) -->
            <div class="font-weight-bold d-lg-none d-dt-none product__final-price">
                <span>{{{PRICE_AFTER_ADDITIONAL_DISCOUNT_FORMATED}}}</span>
            </div>
            {{/SHOW_THREE_PRICES}}
            {{^SHOW_THREE_PRICES}}
            <div class="font-weight-bold d-lg-none d-dt-none {{#showOldPrice}}product__final-price{{/showOldPrice}}"><span>{{{PRICE_FORMATED}}}</span>
	            {{#PRICE_DOLLAR}}
	            &middot; <span>{{PRICE_DOLLAR}}</span>
	            {{/PRICE_DOLLAR}}
            </div>
            {{#showOldPrice}}
            <div class="font-weight-bold d-lg-none d-dt-none cart__price-crossed"><span>{{{oldPriceFormat}}}</span>
                {{#PRICE_DOLLAR}}
                &middot; <span>{{oldPriceDollarFormat}}</span>
                {{/PRICE_DOLLAR}}
            </div>
            {{/showOldPrice}}
            {{/SHOW_THREE_PRICES}}
            <a class="cart__link" href="{{DETAIL_PAGE_URL}}">{{NAME}}</a>
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
        <div>
            {{#SHOW_THREE_PRICES}}
            <!-- Десктопная версия: 3 цены -->
            <!-- 1. Зачеркнутая цена (без скидки) -->
            <div class="d-none d-lg-block cart__price cart__price-crossed">
                <span style="text-decoration: line-through; color: #999;">{{{ORIGINAL_PRICE_FORMATED}}}</span>
            </div>
            {{#ADDITIONAL_DISCOUNT_PERCENT}}
            <!-- 2. Промежуточная зачеркнутая цена (после первой скидки) -->
            <div class="d-none d-lg-block cart__price cart__price-crossed">
                <span style="text-decoration: line-through; color: #999;">{{{PRICE_BEFORE_ADDITIONAL_DISCOUNT_FORMATED}}}</span>
            </div>
            {{/ADDITIONAL_DISCOUNT_PERCENT}}
            <!-- 3. Основная цена (после дополнительной скидки, если есть, или после первой скидки) -->
            <div class="d-none d-lg-block cart__price product__final-price">
                <span>{{{PRICE_AFTER_ADDITIONAL_DISCOUNT_FORMATED}}}</span>
            </div>
            {{/SHOW_THREE_PRICES}}
            {{^SHOW_THREE_PRICES}}
            <div class="d-none d-lg-block cart__price {{#showOldPrice}}product__final-price{{/showOldPrice}}"><span>{{{PRICE_FORMATED}}}</span>
	        {{#PRICE_DOLLAR}}
	        &middot; <span>{{PRICE_DOLLAR}}</span>
	        {{/PRICE_DOLLAR}}
            </div>
            {{#showOldPrice}}
            <div class="d-none d-lg-block cart__price cart__price-crossed"><span>{{{oldPriceFormat}}}</span>
                {{#PRICE_DOLLAR}}
                &middot; <span>{{oldPriceDollarFormat}}</span>
                {{/PRICE_DOLLAR}}
            </div>
            {{/showOldPrice}}
            {{/SHOW_THREE_PRICES}}
        </div>
    </div>
</script>
