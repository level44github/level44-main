<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->IncludeLangFile('template.php');

$cartId = $arParams['cartId'];

require(realpath(dirname(__FILE__)) . '/top_template.php');

use Bitrix\Main\Localization\Loc;

?>
<? if ((int)$arResult['NUM_PRODUCTS'] > 0): ?>
    <div id="<?= $cartId ?>products" class="m-basket__dropdown js-m-basket__dropdown">
        <div class="m-basket__title"><?= Loc::getMessage("BASKET") ?></div>
        <div class="m-basket__items">
            <? foreach ($arResult["CATEGORIES"] as $category => $items): ?>
                <? if (empty($items)):
                    continue;
                    ?>
                <? endif; ?>

                <? foreach ($items as $item): ?>
                    <div class="m-basket__item">
                        <a class="m-basket__image" href="<?= $item["DETAIL_PAGE_URL"] ?>">
                            <img class="img-fluid" src="<?= $item["PICTURE_SRC"] ?>" alt="">
                        </a>
                        <div class="m-basket__body">
                            <a href="<?= $item["DETAIL_PAGE_URL"] ?>">
                                <div><?= $item["NAME"] ?></div>
                                <div class="<?= $item["oldPrice"] ? "product__final-price" : "" ?>">
                                    <span><?= $item["PRICE_FMT"] ?></span>
                                    <? if ($item["PRICE_DOLLAR"]): ?>
                                        &middot; <span><?= $item["PRICE_DOLLAR"] ?></span>
                                    <? endif; ?>
                                </div>
                                <? if (!empty($item["oldPrice"])): ?>
                                    <div class="product__price-crossed"><span><?= $item["oldPriceFormat"] ?></span>
                                        <? if ($item["PRICE_DOLLAR"]): ?>
                                            &middot; <span><?= $item["oldPriceDollarFormat"] ?></span>
                                        <? endif; ?>
                                    </div>
                                <? endif; ?>
                                <? if (!empty($item["SIZE"])): ?>
                                    <div><?= Loc::getMessage("SIZE") ?><?= $item["SIZE"] ?></div>
                                <? endif; ?>
                            </a>
                        </div>
                        <a class="m-basket__remove"
                           href="#"
                           onclick="<?= $cartId ?>.removeItemFromCart(<?= $item['ID'] ?>)"
                        >
                            <svg class="icon icon-close ">
                                <use xlink:href="#close"></use>
                            </svg>
                        </a>
                    </div>
                <? endforeach; ?>
            <? endforeach; ?>
        </div>
        <a class="btn btn-dark btn-block"
           href="<?= $arParams["PATH_TO_BASKET"] ?>"><?= Loc::getMessage("GO_TO_BASKET") ?></a>
    </div>

    <script>
        BX.ready(function () {
            <?=$cartId?>.
            fixCart();
        });
    </script>
<? endif; ?>
