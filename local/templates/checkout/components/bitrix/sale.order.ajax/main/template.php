<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var SaleOrderAjax $component
 * @var string $templateFolder
 */

$context = Main\Application::getInstance()->getContext();
$request = $context->getRequest();

if (strlen($request->get('ORDER_ID')) > 0):
    include(Main\Application::getDocumentRoot() . $templateFolder . '/confirm.php');
    ?>
<? else: ?>
    <form class="row">
        <div class="col-lg-8">
            <h1 class="page__title">Оформление заказа</h1>
            <fieldset class="fieldset">
                <legend>1. Контактные данные</legend>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="form-email">Эл. почта</label>
                            <input class="form-control js-form__control js-form__email" type="email" id="form-email"
                                   placeholder="Введите эл. почту">
                            <div class="invalid-feedback">Недопустимые символы в поле</div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="form-phone">Номер телефона</label>
                            <input class="form-control js-form__control js-form__phone" type="text" id="form-phone"
                                   placeholder="Введите телефона">
                            <div class="invalid-feedback">Недопустимые символы в поле</div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="form-full-name">Имя и фамилия</label>
                    <input class="form-control js-form__control" type="text" id="form-full-name"
                           placeholder="Введите имя">
                </div>
            </fieldset>
            <fieldset class="fieldset">
                <legend>2. Доставка</legend>
                <div class="accordion" id="delivery">
                    <div class="card option"><a class="option__header" data-toggle="collapse" href="#delivery1"
                                                role="button" aria-expanded="false" aria-controls="delivery1">
                            <div class="option__title">Курьером по Москве</div>
                            <div class="option__hint">1 день, бесплатно</div>
                        </a>
                        <div class="collapse" id="delivery1" data-parent="#delivery">
                            <div class="option__body">
                                <div class="form-group">
                                    <label for="form-delivery1-address">Адрес</label>
                                    <input class="form-control js-form__control" type="text" id="form-delivery1-address"
                                           placeholder="Введите адрес">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card option"><a class="option__header" data-toggle="collapse" href="#delivery2"
                                                role="button" aria-expanded="false" aria-controls="delivery2">
                            <div class="option__title">Доставка по России СДЭК (курьером)</div>
                        </a>
                        <div class="collapse" id="delivery2" data-parent="#delivery">
                            <div class="option__body">
                                <p>Введите город и адрес для расчета стоимости доставки</p>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="form-delivery2-city">Город</label>
                                            <input class="form-control js-form__control" type="text"
                                                   id="form-delivery2-city" placeholder="Введите город">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="form-delivery2-address">Адрес</label>
                                    <input class="form-control js-form__control" type="text" id="form-delivery2-address"
                                           placeholder="Введите адрес">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card option"><a class="option__header" data-toggle="collapse" href="#delivery3"
                                                role="button" aria-expanded="false" aria-controls="delivery3">
                            <div class="option__title">Международная доставка EMS (курьером)</div>
                        </a>
                        <div class="collapse" id="delivery3" data-parent="#delivery">
                            <div class="option__body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="form-delivery3-city">Страна</label>
                                            <input class="form-control js-form__control" type="text"
                                                   id="form-delivery3-city" placeholder="Введите страну">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="form-delivery3-address">Город</label>
                                            <input class="form-control js-form__control" type="text"
                                                   id="form-delivery3-address" placeholder="Введите город">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="form-delivery3-address">Адрес</label>
                                    <input class="form-control js-form__control" type="text" id="form-delivery3-address"
                                           placeholder="Введите адрес">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="form-comment">Комментарий</label>
                    <textarea class="form-control" id="form-comment"
                              placeholder="Особые требования к упаковке или доставке..." rows="3"></textarea>
                </div>
            </fieldset>
            <fieldset class="fieldset">
                <legend>3. Оплата</legend>
                <div class="accordion" id="payment">
                    <div class="card option"><a class="option__header" data-toggle="collapse" href="#payment1"
                                                role="button" aria-expanded="false" aria-controls="payment1">
                            <div class="option__title">Наличными курьеру</div>
                        </a>
                        <div class="collapse" id="payment1" data-parent="#payment">
                            <div class="option__body">
                                <div class="form-group">
                                    <label for="form-payment1-address">Адрес</label>
                                    <input class="form-control js-form__control" type="text" id="form-payment1-address"
                                           placeholder="Введите адрес">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card option"><a class="option__header" data-toggle="collapse" href="#payment2"
                                                role="button" aria-expanded="false" aria-controls="payment2">
                            <div class="option__title">Яндекс Деньгами</div>
                        </a>
                        <div class="collapse" id="payment2" data-parent="#payment">
                            <div class="option__body">
                                <p>Hello!</p>
                            </div>
                        </div>
                    </div>
                    <div class="card option"><a class="option__header" data-toggle="collapse" href="#payment3"
                                                role="button" aria-expanded="false" aria-controls="payment3">
                            <div class="option__title">Банковской картой</div>
                        </a>
                        <div class="collapse" id="payment3" data-parent="#payment">
                            <div class="option__body">
                                <p>Hello!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
            <div class="d-none d-lg-block">
                <div class="form-group">
                    <button class="btn btn-dark btn__fix-width" type="submit">Оформить заказ</button>
                </div>
                <p class="text-muted">
                    Нажимая кнопку «Оформить заказ», вы соглашаетесь с
                    <a href="#">публичной офертой</a>
                </p>
            </div>
        </div>
        <div class="col-lg-4">
            <h3 class="aside__title">Состав заказа</h3>
            <div class="card mb-4">
                <div class="basket-aside">
                    <div>
                        <div class="basket-aside__item">
                            <div class="basket-aside__image"><img class="img-fluid" src="img/basket-aside.jpg" alt="">
                            </div>
                            <div class="basket-aside__body">
                                <div class="font-weight-bold">14 800 руб.</div>
                                <div>Жакет классический</div>
                                <ul class="basket-aside__list">
                                    <li>Цвет: Шоколад</li>
                                    <li>Размер: S</li>
                                </ul>
                            </div>
                        </div>
                        <div class="basket-aside__item">
                            <div class="basket-aside__image"><img class="img-fluid" src="img/basket-aside.jpg" alt="">
                            </div>
                            <div class="basket-aside__body">
                                <div class="font-weight-bold">14 800 руб.</div>
                                <div>Жакет классический</div>
                                <ul class="basket-aside__list">
                                    <li>Цвет: Шоколад</li>
                                    <li>Размер: S</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="basket-aside__footer">
                        <div class="d-flex">
                            Товары

                            <span class="basket-aside__pieces">1 шт</span>
                            <div class="ml-auto">14 800 руб.</div>
                        </div>
                        <div class="d-flex">Доставка
                            <div class="ml-auto">0 руб.</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div>Итого без доставки</div>
                        <div class="basket-aside__total">14 800 руб.</div>
                    </div>
                </div>
            </div>
            <div class="d-lg-none">
                <div class="form-group">
                    <button class="btn btn-dark btn-block" type="submit">Перейти к оформлению заказа</button>
                </div>
                <p class="text-muted">
                    Нажимая кнопку «Оформить заказ», вы соглашаетесь с
                    <a href="#">публичной офертой</a>
                </p>
            </div>
        </div>
    </form>
<? endif; ?>