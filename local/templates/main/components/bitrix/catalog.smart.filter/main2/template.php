<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * @global CMain $APPLICATION
 * @global array $arParams
 */
?>




<form class="catalog__desktop-form" action="/apply-filters" method="GET">
    <div class="catalog__desktop-filters">
        <div class="catalog__desktop-filters-wrapper">

            <div class="dropdown dropdown--left" data-dropdown>
                <div class="dropdown__header" role="button"><span
                            class="dropdown__title">Размер одежды</span><span
                            class="dropdown__counter hidden">1</span>
                    <svg class="icon icon-arrow-down dropdown__icon">
                        <use xlink:href="#arrow-down"></use>
                    </svg>
                </div>
                <div class="dropdown__content">
                    <div class="catalog__filter-group">
                        <label class="form-checkbox-desktop" for="size-one-size">
                            <input type="checkbox" id="size-one-size" name="size" value="One size" checked><span
                                    class="form-checkbox-desktop__label">One size</span>
                            <svg class="icon icon-close-small icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-checkbox-desktop" for="size-xs">
                            <input type="checkbox" id="size-xs" name="size" value="XS"><span
                                    class="form-checkbox-desktop__label">XS</span>
                            <svg class="icon icon-close-small icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-checkbox-desktop" for="size-s">
                            <input type="checkbox" id="size-s" name="size" value="S"><span
                                    class="form-checkbox-desktop__label">S</span>
                            <svg class="icon icon-close-small icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-checkbox-desktop" for="size-m">
                            <input type="checkbox" id="size-m" name="size" value="M"><span
                                    class="form-checkbox-desktop__label">M</span>
                            <svg class="icon icon-close-small icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-checkbox-desktop" for="size-l">
                            <input type="checkbox" id="size-l" name="size" value="L"><span
                                    class="form-checkbox-desktop__label">L</span>
                            <svg class="icon icon-close-small icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-checkbox-desktop" for="size-xl">
                            <input type="checkbox" id="size-xl" name="size" value="XL"><span
                                    class="form-checkbox-desktop__label">XL</span>
                            <svg class="icon icon-close-small icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                    </div>
                </div>
            </div>
            <div class="dropdown dropdown--left" data-dropdown>
                <div class="dropdown__header" role="button"><span class="dropdown__title">Материал</span><span
                            class="dropdown__counter hidden">1</span>
                    <svg class="icon icon-arrow-down dropdown__icon">
                        <use xlink:href="#arrow-down"></use>
                    </svg>
                </div>
                <div class="dropdown__content">
                    <div class="catalog__filter-group">
                        <label class="form-checkbox-desktop" for="material-alpaca">
                            <input type="checkbox" id="material-alpaca" name="material" value="Альпака"><span
                                    class="form-checkbox-desktop__label">Альпака</span>
                            <svg class="icon icon-close-small icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-checkbox-desktop" for="material-velvet">
                            <input type="checkbox" id="material-velvet" name="material" value="Вельвет и велюр"><span
                                    class="form-checkbox-desktop__label">Вельвет и велюр</span>
                            <svg class="icon icon-close-small icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-checkbox-desktop" for="material-cashmere">
                            <input type="checkbox" id="material-cashmere" name="material" value="Кашемир"><span
                                    class="form-checkbox-desktop__label">Кашемир</span>
                            <svg class="icon icon-close-small icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-checkbox-desktop" for="material-leather">
                            <input type="checkbox" id="material-leather" name="material"
                                   value="Кожа и замша"><span
                                    class="form-checkbox-desktop__label">Кожа и замша</span>
                            <svg class="icon icon-close-small icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-checkbox-desktop" for="material-knitwear">
                            <input type="checkbox" id="material-knitwear" name="material" value="Трикотаж"><span
                                    class="form-checkbox-desktop__label">Трикотаж</span>
                            <svg class="icon icon-close-small icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-checkbox-desktop" for="material-fleece">
                            <input type="checkbox" id="material-fleece" name="material" value="Флис"><span
                                    class="form-checkbox-desktop__label">Флис</span>
                            <svg class="icon icon-close-small icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-checkbox-desktop" for="material-footer">
                            <input type="checkbox" id="material-footer" name="material" value="Футер"><span
                                    class="form-checkbox-desktop__label">Футер</span>
                            <svg class="icon icon-close-small icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-checkbox-desktop" for="material-silk">
                            <input type="checkbox" id="material-silk" name="material" value="Шелк"><span
                                    class="form-checkbox-desktop__label">Шелк</span>
                            <svg class="icon icon-close-small icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-checkbox-desktop" for="material-wool">
                            <input type="checkbox" id="material-wool" name="material" value="Шерсть"><span
                                    class="form-checkbox-desktop__label">Шерсть</span>
                            <svg class="icon icon-close-small icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                    </div>
                </div>
            </div>
            <div class="dropdown dropdown--left" data-dropdown>
                <div class="dropdown__header" role="button"><span class="dropdown__title">Цвет</span><span
                            class="dropdown__counter hidden">1</span>
                    <svg class="icon icon-arrow-down dropdown__icon">
                        <use xlink:href="#arrow-down"></use>
                    </svg>
                </div>
                <div class="dropdown__content">
                    <div class="catalog__filter-group">
                        <label class="form-color">
                            <input type="checkbox" id="color-beige" name="color" value="Бежевый"><span
                                    class="swatch" style="background-color: #D0C0B0"></span><span
                                    class="label-text">Бежевый</span>
                            <svg class="icon icon-close-small form-color__icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-color">
                            <input type="checkbox" id="color-green" name="color" value="Зеленый"><span
                                    class="swatch" style="background-color: #4CAF50"></span><span
                                    class="label-text">Зеленый</span>
                            <svg class="icon icon-close-small form-color__icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-color">
                            <input type="checkbox" id="color-coffee" name="color" value="Кофейный"><span
                                    class="swatch" style="background-color: #7B5E57"></span><span
                                    class="label-text">Кофейный</span>
                            <svg class="icon icon-close-small form-color__icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-color">
                            <input type="checkbox" id="color-red" name="color" value="Красный"><span
                                    class="swatch" style="background-color: #D32F2F"></span><span
                                    class="label-text">Красный</span>
                            <svg class="icon icon-close-small form-color__icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-color">
                            <input type="checkbox" id="color-milk" name="color" value="Молочный"><span
                                    class="swatch" style="background-color: #FAF6ED"></span><span
                                    class="label-text">Молочный</span>
                            <svg class="icon icon-close-small form-color__icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-color">
                            <input type="checkbox" id="color-pink" name="color" value="Розовый"><span
                                    class="swatch" style="background-color: #FFCBE1"></span><span
                                    class="label-text">Розовый</span>
                            <svg class="icon icon-close-small form-color__icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-color">
                            <input type="checkbox" id="color-gray" name="color" value="Серый"><span
                                    class="swatch" style="background-color: #9E9E9E"></span><span
                                    class="label-text">Серый</span>
                            <svg class="icon icon-close-small form-color__icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-color">
                            <input type="checkbox" id="color-blue" name="color" value="Синий"><span
                                    class="swatch" style="background-color: #3F51B5"></span><span
                                    class="label-text">Синий</span>
                            <svg class="icon icon-close-small form-color__icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-color">
                            <input type="checkbox" id="color-black" name="color" value="Черный"><span
                                    class="swatch" style="background-color: #1A1718"></span><span
                                    class="label-text">Черный</span>
                            <svg class="icon icon-close-small form-color__icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                        <label class="form-color">
                            <input type="checkbox" id="color-chocolate" name="color" value="Шоколадный"><span
                                    class="swatch" style="background-color: #5A3E36"></span><span
                                    class="label-text">Шоколадный</span>
                            <svg class="icon icon-close-small form-color__icon">
                                <use xlink:href="#close-small"></use>
                            </svg>
                        </label>
                    </div>
                </div>
            </div>
            <div class="dropdown dropdown--left" data-dropdown>
                <div class="dropdown__header" role="button"><span class="dropdown__title">Цена</span><span
                            class="dropdown__counter hidden">1</span>
                    <svg class="icon icon-arrow-down dropdown__icon">
                        <use xlink:href="#arrow-down"></use>
                    </svg>
                </div>
                <div class="dropdown__content">
                    <div class="catalog__price-inputs">
                        <div class="form-group">
                            <label for="form-price_min"></label>
                            <input class="form-control js-form__control" type="number" id="form-price_min"
                                   placeholder="От 4000">
                        </div>
                        <div class="separator"></div>
                        <div class="form-group">
                            <label for="form-price_max"></label>
                            <input class="form-control js-form__control" type="number" id="form-price_max"
                                   placeholder="До 54000">
                        </div>
                    </div>
                    <button class="btn btn-dark catalog__price-change-button" type="submit">Применить</button>
                </div>
            </div>

        </div>
        <div>
            <div class="dropdown dropdown--right" data-dropdown id="">
                <div class="dropdown__header" role="button"><span
                            class="dropdown__title"><?= current(array_filter($arParams["SORT_LIST"], fn($item) => $item["selected"]))['name'] ?></span><span
                            class="dropdown__counter hidden">1</span>
                    <svg class="icon icon-arrow-down dropdown__icon">
                        <use xlink:href="#arrow-down"></use>
                    </svg>
                </div>
                <div class="dropdown__content">
                    <div class="catalog__filter-group">
                        <div class="form-group">
                            <label class="radio-label" for="form-sort">Сортировать</label>
                            <div class="form-radio-group catalog__mobile-radio-group">
                                <? foreach ($arParams["SORT_LIST"] as $sortItem): ?>
                                    <label class="form-radio">
                                        <input type="radio"
                                               id="form-sort-<?= $sortItem["code"] ?>"
                                               name="sort"
                                               value="<?= $sortItem["code"] ?>"
                                            <?= $sortItem["selected"] ? "checked" : '' ?>
                                        >
                                        <span><?= $sortItem["name"] ?></span>
                                    </label>
                                <? endforeach; ?>
                            </div>
                            <div class="invalid-feedback">Пожалуйста, выберите значение</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<? ob_start(); ?>
<div class="bottom-sheet" id="filters-sheet">
    <div class="bottom-sheet__overlay"></div>
    <div class="bottom-sheet__container">
        <div class="bottom-sheet__header">
            <h2>Фильтры</h2>
            <button class="btn btn-text bottom-sheet__close" type="button" aria-label="Close">
                <svg class="icon icon-close bottom-sheet__close-icon">
                    <use xlink:href="#close"></use>
                </svg>
            </button>
        </div>
        <div class="bottom-sheet__content">
            <form id="filters-form" class="js-mobile-filters"
                  action="<?= $APPLICATION->GetCurPageParam('', ['sort']) ?>" method="GET">
                <div class="form-group">
                    <label class="radio-label" for="form-sort">Сортировать</label>
                    <div class="form-radio-group catalog__mobile-radio-group">
                        <? foreach ($arParams["SORT_LIST"] as $sortItem): ?>
                            <label class="form-radio">
                                <input type="radio"
                                       id="form-sort-<?= $sortItem["code"] ?>"
                                       name="sort"
                                       value="<?= $sortItem["code"] ?>"
                                    <?= $sortItem["selected"] ? "checked" : '' ?>
                                >
                                <span><?= $sortItem["name"] ?></span>
                            </label>
                        <? endforeach; ?>
                    </div>
                    <div class="invalid-feedback">Пожалуйста, выберите значение</div>
                </div>

                <div class="catalog__mobile-filters">
                    <div class="accordion">
                        <button class="btn btn-link accordion__trigger" type="button"
                                aria-label="Toggle accordion">
                            <div class="accordion__title">Размер одежды</div>
                            <svg class="icon icon-arrow-down accordion__icon">
                                <use xlink:href="#arrow-down"></use>
                            </svg>
                        </button>
                        <div class="accordion__content">
                            <div class="catalog__mobile-input-group">
                                <label class="form-checkbox">
                                    <input type="checkbox" id="size-one-size" name="size"
                                           value="One size"><span>One size</span>
                                </label>
                                <label class="form-checkbox">
                                    <input type="checkbox" id="size-xs" name="size" value="XS"><span>XS</span>
                                </label>
                                <label class="form-checkbox">
                                    <input type="checkbox" id="size-s" name="size" value="S"><span>S</span>
                                </label>
                                <label class="form-checkbox">
                                    <input type="checkbox" id="size-m" name="size" value="M"><span>M</span>
                                </label>
                                <label class="form-checkbox">
                                    <input type="checkbox" id="size-l" name="size" value="L"><span>L</span>
                                </label>
                                <label class="form-checkbox">
                                    <input type="checkbox" id="size-xl" name="size" value="XL"><span>XL</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="accordion">
                        <button class="btn btn-link accordion__trigger" type="button"
                                aria-label="Toggle accordion">
                            <div class="accordion__title">Материал</div>
                            <svg class="icon icon-arrow-down accordion__icon">
                                <use xlink:href="#arrow-down"></use>
                            </svg>
                        </button>
                        <div class="accordion__content">
                            <div class="catalog__mobile-input-group">
                                <label class="form-checkbox">
                                    <input type="checkbox" id="material-alpaca" name="material" value="Альпака"><span>Альпака</span>
                                </label>
                                <label class="form-checkbox">
                                    <input type="checkbox" id="material-velvet" name="material"
                                           value="Вельвет и велюр"><span>Вельвет и велюр</span>
                                </label>
                                <label class="form-checkbox">
                                    <input type="checkbox" id="material-cashmere" name="material"
                                           value="Кашемир"><span>Кашемир</span>
                                </label>
                                <label class="form-checkbox">
                                    <input type="checkbox" id="material-leather" name="material"
                                           value="Кожа и замша"><span>Кожа и замша</span>
                                </label>
                                <label class="form-checkbox">
                                    <input type="checkbox" id="material-knitwear" name="material"
                                           value="Трикотаж"><span>Трикотаж</span>
                                </label>
                                <label class="form-checkbox">
                                    <input type="checkbox" id="material-fleece" name="material"
                                           value="Флис"><span>Флис</span>
                                </label>
                                <label class="form-checkbox">
                                    <input type="checkbox" id="material-footer" name="material"
                                           value="Футер"><span>Футер</span>
                                </label>
                                <label class="form-checkbox">
                                    <input type="checkbox" id="material-silk" name="material"
                                           value="Шелк"><span>Шелк</span>
                                </label>
                                <label class="form-checkbox">
                                    <input type="checkbox" id="material-wool" name="material"
                                           value="Шерсть"><span>Шерсть</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="accordion">
                        <button class="btn btn-link accordion__trigger" type="button"
                                aria-label="Toggle accordion">
                            <div class="accordion__title">Цвет</div>
                            <svg class="icon icon-arrow-down accordion__icon">
                                <use xlink:href="#arrow-down"></use>
                            </svg>
                        </button>
                        <div class="accordion__content">
                            <div class="catalog__mobile-input-group">
                                <label class="form-color">
                                    <input type="checkbox" id="color-beige" name="color" value="Бежевый"><span
                                            class="swatch" style="background-color: #D0C0B0"></span><span
                                            class="label-text">Бежевый</span>
                                    <svg class="icon icon-close-small form-color__icon">
                                        <use xlink:href="#close-small"></use>
                                    </svg>
                                </label>
                                <label class="form-color">
                                    <input type="checkbox" id="color-green" name="color" value="Зеленый"><span
                                            class="swatch" style="background-color: #4CAF50"></span><span
                                            class="label-text">Зеленый</span>
                                    <svg class="icon icon-close-small form-color__icon">
                                        <use xlink:href="#close-small"></use>
                                    </svg>
                                </label>
                                <label class="form-color">
                                    <input type="checkbox" id="color-coffee" name="color" value="Кофейный"><span
                                            class="swatch" style="background-color: #7B5E57"></span><span
                                            class="label-text">Кофейный</span>
                                    <svg class="icon icon-close-small form-color__icon">
                                        <use xlink:href="#close-small"></use>
                                    </svg>
                                </label>
                                <label class="form-color">
                                    <input type="checkbox" id="color-red" name="color" value="Красный"><span
                                            class="swatch" style="background-color: #D32F2F"></span><span
                                            class="label-text">Красный</span>
                                    <svg class="icon icon-close-small form-color__icon">
                                        <use xlink:href="#close-small"></use>
                                    </svg>
                                </label>
                                <label class="form-color">
                                    <input type="checkbox" id="color-milk" name="color" value="Молочный"><span
                                            class="swatch" style="background-color: #FAF6ED"></span><span
                                            class="label-text">Молочный</span>
                                    <svg class="icon icon-close-small form-color__icon">
                                        <use xlink:href="#close-small"></use>
                                    </svg>
                                </label>
                                <label class="form-color">
                                    <input type="checkbox" id="color-pink" name="color" value="Розовый"><span
                                            class="swatch" style="background-color: #FFCBE1"></span><span
                                            class="label-text">Розовый</span>
                                    <svg class="icon icon-close-small form-color__icon">
                                        <use xlink:href="#close-small"></use>
                                    </svg>
                                </label>
                                <label class="form-color">
                                    <input type="checkbox" id="color-gray" name="color" value="Серый"><span
                                            class="swatch" style="background-color: #9E9E9E"></span><span
                                            class="label-text">Серый</span>
                                    <svg class="icon icon-close-small form-color__icon">
                                        <use xlink:href="#close-small"></use>
                                    </svg>
                                </label>
                                <label class="form-color">
                                    <input type="checkbox" id="color-blue" name="color" value="Синий"><span
                                            class="swatch" style="background-color: #3F51B5"></span><span
                                            class="label-text">Синий</span>
                                    <svg class="icon icon-close-small form-color__icon">
                                        <use xlink:href="#close-small"></use>
                                    </svg>
                                </label>
                                <label class="form-color">
                                    <input type="checkbox" id="color-black" name="color" value="Черный"><span
                                            class="swatch" style="background-color: #1A1718"></span><span
                                            class="label-text">Черный</span>
                                    <svg class="icon icon-close-small form-color__icon">
                                        <use xlink:href="#close-small"></use>
                                    </svg>
                                </label>
                                <label class="form-color">
                                    <input type="checkbox" id="color-chocolate" name="color" value="Шоколадный"><span
                                            class="swatch" style="background-color: #5A3E36"></span><span
                                            class="label-text">Шоколадный</span>
                                    <svg class="icon icon-close-small form-color__icon">
                                        <use xlink:href="#close-small"></use>
                                    </svg>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="accordion">
                        <button class="btn btn-link accordion__trigger" type="button"
                                aria-label="Toggle accordion">
                            <div class="accordion__title">Цена</div>
                            <svg class="icon icon-arrow-down accordion__icon">
                                <use xlink:href="#arrow-down"></use>
                            </svg>
                        </button>
                        <div class="accordion__content">
                            <div class="catalog__mobile-price-inputs">
                                <div class="form-group">
                                    <label for="form-price_min"></label>
                                    <input class="form-control js-form__control" type="number"
                                           id="form-price_min" placeholder="От 4000">
                                </div>
                                <div class="separator"></div>
                                <div class="form-group">
                                    <label for="form-price_max"></label>
                                    <input class="form-control js-form__control" type="number"
                                           id="form-price_max" placeholder="До 54000">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div></div>
                </div>

                <div class="catalog__mobile-buttons">
                    <button class="btn btn-light" type="clear">Сбросить</button>
                    <button class="btn btn-dark" type="submit">Показать</button>
                </div>
            </form>
        </div>
    </div>
</div>
<? $APPLICATION->AddViewContent("catalog-filters", ob_get_clean());
?>
