<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @global array $arParams
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global string $cartId
 */
?>
<a class="btn btn-link menu__link m-basket-link" href="<?= $arParams["PATH_TO_BASKET"] ?>" role="button"
   data-toggle="dropdown">
    <svg class="icon icon-basket menu__icon">
        <use xlink:href="#basket"></use>
    </svg>
    <? if ((int)$arResult['NUM_PRODUCTS'] > 0): ?>
        <div class="menu__basket-count"><?= $arResult['NUM_PRODUCTS'] ?></div>
    <? endif; ?>
</a>
