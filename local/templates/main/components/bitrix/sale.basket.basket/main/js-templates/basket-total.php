<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

/**
 * @var array $arParams
 */
?>
<script id="basket-total-template" type="text/html">
    <div class="d-flex mb-3">
        Товары

        <span class="basket-aside__pieces">{{QUANTITY}} шт</span>
        <div class="ml-auto" data-entity="basket-total-price">{{{PRICE_FORMATED}}}</div>
    </div>
    <a class="btn btn-dark btn-block" href="<?= $arParams["PATH_TO_ORDER"] ?>">Перейти к оформлению заказа</a>
</script>
