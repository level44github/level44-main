<?php

namespace Level44\Event;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Order;
use Bitrix\Currency\CurrencyManager;

/**
 * Подарок при сумме заказа от 40 000 ₽.
 * Добавляется только в заказ при переходе в checkout, в корзине не отображается.
 * Товары-подарки по приоритету: 3275, 3273, 3272 (первый с остатком).
 */
class GiftOver40kHandlers extends HandlerBase
{
    private const THRESHOLD_SUM = 40000;
    private const GIFT_PRODUCT_IDS = [3275, 3273, 3272];

    public static function register(): void
    {
        static::addEventHandler('sale', 'OnSaleOrderBeforeSaved', sort: 5);
    }

    /**
     * Добавление подарка в корзину заказа только при сохранении заказа (checkout).
     */
    public static function OnSaleOrderBeforeSavedHandler(Event $event): ?EventResult
    {
        if (!Loader::includeModule('sale') || !Loader::includeModule('catalog')) {
            return null;
        }

        /** @var Order $order */
        $order = $event->getParameter('ENTITY');
        if (!$order instanceof Order) {
            return null;
        }

        $basket = $order->getBasket();
        if (!$basket instanceof Basket) {
            return null;
        }

        $sumWithoutGifts = 0;
        $giftItem = null;

        foreach ($basket->getBasketItems() as $item) {
            $productId = (int) $item->getProductId();
            if (in_array($productId, self::GIFT_PRODUCT_IDS, true)) {
                $giftItem = $item;
            } else {
                $sumWithoutGifts += $item->getPrice() * $item->getQuantity();
            }
        }

        if ($sumWithoutGifts >= self::THRESHOLD_SUM) {
            if ($giftItem === null) {
                $giftProductId = self::getFirstAvailableGiftProductId();
                if ($giftProductId !== null) {
                    self::addGiftToBasket($basket, $giftProductId);
                }
            }
        } elseif ($giftItem !== null) {
            $giftItem->delete();
        }

        return new EventResult(EventResult::SUCCESS);
    }

    private static function getFirstAvailableGiftProductId(): ?int
    {
        foreach (self::GIFT_PRODUCT_IDS as $productId) {
            $product = \CCatalogProduct::GetByID($productId);
            if (!is_array($product)) {
                continue;
            }
            $quantity = (float) ($product['QUANTITY'] ?? 0);
            if ($quantity > 0) {
                return $productId;
            }
        }
        return null;
    }

    private static function addGiftToBasket(Basket $basket, int $productId): void
    {
        $currency = CurrencyManager::getBaseCurrency() ?: 'RUB';
        $item = $basket->createItem('catalog', $productId);
        $item->setFields([
            'QUANTITY' => 1,
            'CURRENCY' => $currency,
            'LID' => $basket->getSiteId(),
            'PRICE' => 0,
            'CUSTOM_PRICE' => 'Y',
        ]);
    }
}
