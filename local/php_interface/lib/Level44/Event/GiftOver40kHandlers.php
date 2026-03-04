<?php

namespace Level44\Event;

use Bitrix\Main\Context;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Fuser;
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
        static::addEventHandler('main', 'OnProlog', sort: 10);
        static::addEventHandler('sale', 'OnSaleBasketBeforeSaved', sort: 50);
    }

    /**
     * Синхронизация подарка при загрузке страниц корзины и оформления заказа
     * (OnSaleBasketBeforeSaved может не вызываться при работе с корзиной в их конфигурации).
     */
    public static function OnPrologHandler(): void
    {
        $request = Context::getCurrent()->getRequest();
        $uri = (string) $request->getRequestUri();
        $path = parse_url($uri, PHP_URL_PATH);
        if ($path === null || $path === '') {
            return;
        }
        // Только на страницах /cart и /checkout (с учётом подпапок, например /en/cart/)
        if (!preg_match('#/(?:cart|checkout)(?:/|$)#', $path)) {
            return;
        }

        if (!Loader::includeModule('sale') || !Loader::includeModule('catalog')) {
            return;
        }

        $siteId = Context::getCurrent()->getSite();
        if (!$siteId) {
            return;
        }

        $basket = Basket::loadItemsForFUser(Fuser::getId(), $siteId);
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
                    self::addGiftByApi($giftProductId, $siteId);
                }
            }
        } elseif ($giftItem !== null) {
            $giftItem->delete();
            $basket->save();
        }
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

    /**
     * Возвращает ID товара/оффера для добавления в корзину (первый с остатком).
     * Поддерживаются и ID товара, и ID торгового предложения.
     */
    private static function getFirstAvailableGiftProductId(): ?int
    {
        foreach (self::GIFT_PRODUCT_IDS as $id) {
            $product = \CCatalogProduct::GetByID($id);
            if (is_array($product)) {
                $quantity = (float) ($product['QUANTITY'] ?? 0);
                if ($quantity > 0) {
                    return $id;
                }
            }
            // Возможно, это ID товара — ищем первый оффер с остатком
            $offers = \CCatalogSKU::getOffersList($id);
            $offerList = isset($offers[$id]) && is_array($offers[$id]) ? $offers[$id] : [];
            if (empty($offerList)) {
                continue;
            }
            foreach (array_keys($offerList) as $offerId) {
                $offerProduct = \CCatalogProduct::GetByID($offerId);
                if (is_array($offerProduct)) {
                    $quantity = (float) ($offerProduct['QUANTITY'] ?? 0);
                    if ($quantity > 0) {
                        return (int) $offerId;
                    }
                }
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
     * Добавление подарка через API корзины (для OnProlog, когда корзина загружается отдельно).
     */
    private static function addGiftByApi(int $productId, string $siteId): void
    {
        $currency = CurrencyManager::getBaseCurrency() ?: 'RUB';
        $product = [
            'PRODUCT_ID' => $productId,
            'QUANTITY' => 1,
        ];
        $basketFields = [
            'PRICE' => 0,
            'CURRENCY' => $currency,
            'LID' => $siteId,
            'CUSTOM_PRICE' => 'Y',
        ];
        \Bitrix\Catalog\Product\Basket::addProduct($product, $basketFields);
    }

    /**
     * Проверка: является ли позиция подарком (по ID и нулевой цене).
     */
    public static function isGiftItem(int $productId, float $price): bool
    {
        return in_array($productId, self::GIFT_PRODUCT_IDS, true) && $price == 0;
    }

    /**
     * Синхронизация подарка для текущей корзины пользователя.
     * Вызывать из result_modifier корзины или при загрузке checkout.
     * Возвращает true, если был добавлен подарок (нужен редирект для перезагрузки корзины).
     */
    public static function syncGiftForCurrentBasket(): bool
    {
        if (!Loader::includeModule('sale') || !Loader::includeModule('catalog')) {
            return false;
        }
        $siteId = Context::getCurrent()->getSite();
        if (!$siteId) {
            return false;
        }
        $basket = Basket::loadItemsForFUser(Fuser::getId(), $siteId);
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
                    self::addGiftByApi($giftProductId, $siteId);
                    return true;
                }
            }
        } elseif ($giftItem !== null) {
            $giftItem->delete();
            $basket->save();
        }
        return false;
    }
}
