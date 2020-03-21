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
        <div class="ml-auto" data-entity="basket-total-price"><span>{{{PRICE_FORMATED}}}</span>&middot;<span> $ 120</span></div>
    </div>
    <a class="btn btn-dark btn-block" href="<?= $arParams["PATH_TO_ORDER"] ?>"><?= Loc::getMessage("PROCEED_TO_CHECKOUT") ?></a>
</script>
