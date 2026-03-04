<?php

namespace Level44\Event;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use Bitrix\Sale\Basket;
use Bitrix\Currency\CurrencyManager;

/**
 * Подарок в корзину при сумме заказа от 40 000 ₽.
 * Товары-подарки по приоритету: 3275, 3273, 3272 (первый с остатком).
 * В шаблоне корзины подарочные товары (по ID и цене 0) скрываются от отображения.
 */
class GiftOver40kHandlers extends HandlerBase
{
    public const THRESHOLD_SUM = 40000;
    public const GIFT_PRODUCT_IDS = [3275, 3273, 3272];

    public static function register(): void
    {
        static::addEventHandler('sale', 'OnSaleBasketBeforeSaved', sort: 50);
    }

    public static function OnSaleBasketBeforeSavedHandler(Event $event): ?EventResult
    {
        if (!Loader::includeModule('sale') || !Loader::includeModule('catalog')) {
            return null;
        }

        /** @var Basket $basket */
        $basket = $event->getParameter('ENTITY');
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
            'PRODUCT_PROVIDER_CLASS' => \Bitrix\Catalog\Product\CatalogProvider::class,
        ]);
    }

    /**
     * Проверка: является ли позиция подарком (по ID и нулевой цене).
     */
    public static function isGiftItem(int $productId, float $price): bool
    {
        return in_array($productId, self::GIFT_PRODUCT_IDS, true) && $price == 0;
    }
}
