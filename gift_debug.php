<?php
/**
 * Диагностика подарков при сумме от 40 000 ₽.
 * Откройте в браузере: /gift_debug.php (с авторизованной сессией и товарами в корзине на 40k+).
 * Удалите файл после отладки.
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

if (!\Bitrix\Main\Loader::includeModule('sale') || !\Bitrix\Main\Loader::includeModule('catalog')) {
    die('Модули sale или catalog не подключены.');
}

header('Content-Type: text/html; charset=utf-8');

$siteId = \Bitrix\Main\Context::getCurrent()->getSite();
$fuserId = \Bitrix\Sale\Fuser::getId();
$basket = \Bitrix\Sale\Basket::loadItemsForFUser($fuserId, $siteId);

$sumWithoutGifts = 0;
$giftItem = null;
$itemsInfo = [];

foreach ($basket->getBasketItems() as $item) {
    $productId = (int) $item->getProductId();
    $row = (int) $item->getQuantity();
    $price = (float) $item->getPrice();
    $isGift = \Level44\Event\GiftOver40kHandlers::isGiftProductOrOffer($productId);
    $itemsInfo[] = compact('productId', 'row', 'price', 'isGift');
    if ($isGift) {
        $giftItem = $item;
    } else {
        $sumWithoutGifts += $price * $row;
    }
}

$giftProductId = \Level44\Event\GiftOver40kHandlers::getFirstAvailableGiftProductId();

echo '<h2>Диагностика подарков</h2>';
echo '<p><strong>SITE_ID:</strong> ' . htmlspecialchars($siteId) . '</p>';
echo '<p><strong>FUSER_ID:</strong> ' . (int) $fuserId . '</p>';
echo '<p><strong>Сумма без подарков:</strong> ' . $sumWithoutGifts . ' ₽ (порог: ' . \Level44\Event\GiftOver40kHandlers::THRESHOLD_SUM . ')</p>';
echo '<p><strong>Подарок уже в корзине:</strong> ' . ($giftItem ? 'да' : 'нет') . '</p>';
echo '<p><strong>ID подарка для добавления (getFirstAvailableGiftProductId):</strong> ' . ($giftProductId === null ? 'null' : $giftProductId) . '</p>';

echo '<h3>Позиции в корзине</h3><pre>' . htmlspecialchars(print_r($itemsInfo, true)) . '</pre>';

if ($giftProductId !== null && $sumWithoutGifts >= \Level44\Event\GiftOver40kHandlers::THRESHOLD_SUM && $giftItem === null) {
    $currency = \Bitrix\Currency\CurrencyManager::getBaseCurrency() ?: 'RUB';
    $product = ['PRODUCT_ID' => $giftProductId, 'QUANTITY' => 1];
    $basketFields = ['PRICE' => 0, 'CURRENCY' => $currency, 'LID' => $siteId, 'CUSTOM_PRICE' => 'Y'];
    $result = \Bitrix\Catalog\Product\Basket::addProduct($product, $basketFields);
    echo '<h3>Попытка addProduct</h3>';
    echo '<p><strong>Успех:</strong> ' . ($result && $result->isSuccess() ? 'да' : 'нет') . '</p>';
    if ($result && !$result->isSuccess()) {
        echo '<p><strong>Ошибки:</strong></p><pre>' . htmlspecialchars(print_r($result->getErrorMessages(), true)) . '</pre>';
    }
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
