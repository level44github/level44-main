<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<? if (!empty($arResult["ITEMS"])): ?>
    <div class="row catalog__items">
        <? foreach ($arResult["ITEMS"] as $item): ?>
            <div class="col-6 col-lg-3 catalog__item">
                <a class="catalog__item-image" href="<?= $item["DETAIL_PAGE_URL"] ?>">
                    <? if (!empty($item["PREVIEW_PICTURE"])): ?>
                        <img class="img-fluid" src="<?= $item["PREVIEW_PICTURE"]["SRC"] ?>" alt="<?= $item["NAME"] ?>">
                    <? endif; ?>
                </a>
                <div class="catalog__item-footer">
                    <a class="catalog__item-title" href="<?= $item["DETAIL_PAGE_URL"] ?>"><?= $item["NAME"] ?></a>
                    <div class="catalog__item-price"><?= $item["ACTUAL_ITEM"]["PRICE"]["PRINT_BASE_PRICE"] ?></div>
                </div>
            </div>
        <? endforeach; ?>
    </div>

<? endif; ?>
