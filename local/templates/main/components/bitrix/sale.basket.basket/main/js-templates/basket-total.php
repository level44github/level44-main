<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

/**
 * @var array $arParams
 */
?>
<script id="basket-total-template" type="text/html">
    <div class="d-flex mb-3"><?= Loc::getMessage("GOODS") ?>
        <span class="basket-aside__pieces"
              data-entity="basket-items-count" data-filter="all"></span>
        <div class="ml-auto">
        <div data-entity="basket-total-price" class="{{#SHOW_OLD_SUM_PRICE}}product__final-price{{/SHOW_OLD_SUM_PRICE}}"><span>{{{PRICE_FORMATED}}}</span>
	        {{#SUM_PRICE_DOLLAR}}
	        &middot; <span>{{{SUM_PRICE_DOLLAR}}}</span>
	        {{/SUM_PRICE_DOLLAR}}
        </div>
            {{#SHOW_OLD_SUM_PRICE}}
            <div class="ml-auto basket-aside__price-crossed" data-entity="basket-total-price">
                <span>{{{OLD_SUM_PRICE}}}</span>
                {{#SUM_PRICE_DOLLAR}}
                &middot; <span>{{{OLD_SUM_PRICE_DOLLAR}}}</span>
                {{/SUM_PRICE_DOLLAR}}
            </div>
            {{/SHOW_OLD_SUM_PRICE}}
</div>
    </div>
    <a class="btn btn-dark btn-block" href="<?= $arParams["PATH_TO_ORDER"] ?>"><?= Loc::getMessage("PROCEED_TO_CHECKOUT") ?></a>
</script>
