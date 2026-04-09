<?php

namespace Level44\Event;

use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Sale\PriceMaths;
use Bitrix\Sale\Order;
use Bitrix\Sale\BasketItem;
use Level44\Base;

/**
 * Обработчик кастомных скидок для товаров категории sale
 *
 * Правила:
 * - 1 товар: нет доп скидки
 * - 2 товара: на товар наименьшей стоимости - 10% скидка
 * - 3 товара: 1 товар (наименьший) - 15%, 2 товар - 10%, 3 товар - нет доп скидки
 * - 4+ товара: скидка 20% на 4-й товар и далее (первые 3 товара без доп скидки)
 */
class SaleCategoryDiscountHandler extends HandlerBase
{
    /**
     * Применить дополнительные скидки к товарам корзины категории sale
     *
     * @param array $basketItems Массив товаров корзины
     * @return array Модифицированный массив товаров с дополнительными полями цен
     */
    public static function applyDiscounts(array $basketItems)
    {
        if (!Loader::includeModule('sale') || !Loader::includeModule('iblock')) {
            return $basketItems;
        }

        // Получаем товары категории sale
        $saleItems = self::getSaleItems($basketItems);

        if (empty($saleItems)) {
            return $basketItems;
        }

        $saleItemsCount = count($saleItems);

        // Рассчитываем скидки
        $discounts = self::calculateDiscounts($saleItems, $saleItemsCount);

        // Группируем скидки по item_id для товаров с QUANTITY > 1
        // Структура: [item_id => [0 => discount, 1 => discount, ...]]
        $discountsByItem = [];
        foreach ($discounts as $itemKey => $discountPercent) {
            // Если itemKey содержит индекс единицы (формат: "item_id_index"), разбираем его
            // Иначе это старый формат, где itemKey = item_id
            if (strpos($itemKey, '_qty_') !== false) {
                list($itemId, $qtyIndex) = explode('_qty_', $itemKey, 2);
                $qtyIndex = (int)$qtyIndex;
                if (!isset($discountsByItem[$itemId])) {
                    $discountsByItem[$itemId] = [];
                }
                $discountsByItem[$itemId][$qtyIndex] = $discountPercent;
            } else {
                // Старый формат - одна запись для всего товара
                $discountsByItem[$itemKey] = [0 => $discountPercent];
            }
        }
        
        // Создаем мапу товаров категории sale для быстрого поиска
        $saleItemIds = [];
        foreach ($saleItems as $saleItem) {
            $saleItemIds[] = $saleItem['item_id'];
        }

        foreach ($basketItems as $key => &$basketItem) {
            // Пробуем разные варианты идентификаторов
            // В чекауте может использоваться ID (числовой) или BASKET_CODE (строка)
            $itemId = null;
            if (isset($basketItem['ID'])) {
                $itemId = $basketItem['ID'];
            } elseif (isset($basketItem['BASKET_ID'])) {
                $itemId = $basketItem['BASKET_ID'];
            } elseif (isset($basketItem['BASKET_CODE'])) {
                $itemId = $basketItem['BASKET_CODE'];
            }

            // Проверяем, относится ли товар к категории sale
            // Сравниваем как строки, так как ID может быть числом или строкой
            if (!$itemId || !in_array($itemId, $saleItemIds, true)) {
                continue;
            }

            // Сохраняем исходную цену БЕЗ всех скидок (BASE_PRICE) для зачеркнутой цены
            $currency = $basketItem['CURRENCY'] ?? $basketItem['CURRENCY_ID'] ?? 'RUB';
            $quantity = $basketItem['QUANTITY'] ?? 1;

            // ORIGINAL_PRICE - это базовая цена БЕЗ всех скидок
            // В чекауте может использоваться oldPrice из result_modifier.php, который берется из свойства OLD_PRICE товара
            // Если oldPrice установлен, используем его, иначе используем BASE_PRICE
            if (isset($basketItem['oldPrice']) && $basketItem['oldPrice'] > 0) {
                // oldPrice уже умножен на количество, нужно разделить
                $originalPrice = $basketItem['oldPrice'] / $quantity;
            } elseif (isset($basketItem['BASE_PRICE']) && $basketItem['BASE_PRICE'] > 0) {
                $originalPrice = $basketItem['BASE_PRICE'];
            } elseif (isset($basketItem['SUM_BASE']) && $basketItem['SUM_BASE'] > 0 && $quantity > 0) {
                $originalPrice = $basketItem['SUM_BASE'] / $quantity;
            } else {
                $originalPrice = $basketItem['PRICE'] ?? 0;
            }

            $basketItem['ORIGINAL_PRICE'] = PriceMaths::roundPrecision($originalPrice);
            $basketItem['ORIGINAL_PRICE_FORMATED'] = \CCurrencyLang::CurrencyFormat(
                $basketItem['ORIGINAL_PRICE'],
                $currency,
                true
            );
            $basketItem['SUM_ORIGINAL_PRICE'] = $basketItem['ORIGINAL_PRICE'] * $quantity;
            $basketItem['SUM_ORIGINAL_PRICE_FORMATED'] = \CCurrencyLang::CurrencyFormat(
                $basketItem['SUM_ORIGINAL_PRICE'],
                $currency,
                true
            );

            // Получаем скидки для этого товара (может быть массив для каждой единицы или одно значение)
            $itemDiscounts = $discountsByItem[$itemId] ?? null;
            
            if (empty($itemDiscounts)) {
                // Товар категории sale, но без доп скидки (например, 1 товар или 3-й товар при 3 товарах)
                // Цена до и после доп скидки одинаковые
                $basketItem['PRICE_BEFORE_ADDITIONAL_DISCOUNT'] = $basketItem['PRICE'];
                $basketItem['PRICE_AFTER_ADDITIONAL_DISCOUNT'] = $basketItem['PRICE'];
                $basketItem['PRICE_BEFORE_ADDITIONAL_DISCOUNT_FORMATED'] = $basketItem['PRICE_FORMATED'];
                $basketItem['PRICE_AFTER_ADDITIONAL_DISCOUNT_FORMATED'] = $basketItem['PRICE_FORMATED'];

                $basketItem['SUM_PRICE_BEFORE_ADDITIONAL_DISCOUNT'] = $basketItem['PRICE'] * $quantity;
                $basketItem['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT'] = $basketItem['PRICE'] * $quantity;
                $basketItem['SUM_PRICE_BEFORE_ADDITIONAL_DISCOUNT_FORMATED'] = $basketItem['SUM'];
                $basketItem['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT_FORMATED'] = $basketItem['SUM'];

                // Флаг для отображения 3 цен
                $basketItem['SHOW_THREE_PRICES'] = true;
                continue;
            }

            // Сохраняем исходную цену БЕЗ всех скидок (BASE_PRICE) для зачеркнутой цены
            // Это нужно сделать ДО применения дополнительной скидки
            $currency = $basketItem['CURRENCY'] ?? $basketItem['CURRENCY_ID'] ?? 'RUB';
            $quantity = $basketItem['QUANTITY'] ?? 1;

            // ORIGINAL_PRICE - это базовая цена БЕЗ всех скидок
            // В чекауте может использоваться oldPrice из result_modifier.php, который берется из свойства OLD_PRICE товара
            // Если oldPrice установлен, используем его, иначе используем BASE_PRICE
            if (isset($basketItem['oldPrice']) && $basketItem['oldPrice'] > 0) {
                // oldPrice уже умножен на количество, нужно разделить
                $originalPrice = $basketItem['oldPrice'] / $quantity;
            } elseif (isset($basketItem['BASE_PRICE']) && $basketItem['BASE_PRICE'] > 0) {
                $originalPrice = $basketItem['BASE_PRICE'];
            } elseif (isset($basketItem['SUM_BASE']) && $basketItem['SUM_BASE'] > 0 && $quantity > 0) {
                $originalPrice = $basketItem['SUM_BASE'] / $quantity;
            } else {
                $originalPrice = $basketItem['PRICE'] ?? 0;
            }

            $basketItem['ORIGINAL_PRICE'] = PriceMaths::roundPrecision($originalPrice);
            $basketItem['ORIGINAL_PRICE_FORMATED'] = \CCurrencyLang::CurrencyFormat(
                $basketItem['ORIGINAL_PRICE'],
                $currency,
                true
            );
            $basketItem['SUM_ORIGINAL_PRICE'] = $basketItem['ORIGINAL_PRICE'] * $quantity;
            $basketItem['SUM_ORIGINAL_PRICE_FORMATED'] = \CCurrencyLang::CurrencyFormat(
                $basketItem['SUM_ORIGINAL_PRICE'],
                $currency,
                true
            );

            // Важно: PRICE может уже содержать дополнительную скидку (если она была применена ранее)
            // Поэтому используем BASE_PRICE как цену после первой скидки (до дополнительной)
            // Если PRICE >= BASE_PRICE, значит дополнительная скидка еще не применялась, используем PRICE
            $currentPrice = $basketItem['PRICE'] ?? 0;
            $basePrice = $basketItem['BASE_PRICE'] ?? $currentPrice;

            // Если PRICE меньше BASE_PRICE, значит дополнительная скидка уже была применена
            // В этом случае используем BASE_PRICE как цену до дополнительной скидки
            if ($currentPrice < $basePrice) {
                $priceBeforeAdditionalDiscount = $basePrice;
            } else {
                // Если PRICE >= BASE_PRICE, используем PRICE (цена после первой скидки, но до дополнительной)
                $priceBeforeAdditionalDiscount = $currentPrice;
            }

            // Получаем скидки для этого товара (может быть массив для каждой единицы или одно значение)
            $itemDiscounts = $discountsByItem[$itemId] ?? null;
            
            // Если товар в количестве > 1, применяем скидку к каждой единице отдельно и усредняем
            if ($quantity > 1 && !empty($itemDiscounts)) {
                // Рассчитываем общую сумму со скидками для всех единиц
                // Каждая единица получает свою скидку (если указана) или 0%
                $totalPriceAfterDiscount = 0;
                $hasDiscount = false;
                
                // Сохраняем информацию о скидках для каждой единицы
                $unitPricesAfterDiscount = [];
                
                for ($i = 0; $i < $quantity; $i++) {
                    $unitDiscountPercent = $itemDiscounts[$i] ?? 0;
                    if ($unitDiscountPercent > 0) {
                        $hasDiscount = true;
                        $unitDiscountAmount = $priceBeforeAdditionalDiscount * $unitDiscountPercent / 100;
                        $unitPriceAfterDiscount = $priceBeforeAdditionalDiscount - $unitDiscountAmount;
                        $unitPricesAfterDiscount[$i] = round($unitPriceAfterDiscount);
                        $totalPriceAfterDiscount += $unitPricesAfterDiscount[$i];
                    } else {
                        $unitPricesAfterDiscount[$i] = $priceBeforeAdditionalDiscount;
                        $totalPriceAfterDiscount += $priceBeforeAdditionalDiscount;
                    }
                }
                
                // Цена за единицу рассчитывается так, чтобы PRICE * QUANTITY = общая сумма со скидками
                // Это обеспечит правильную сумму при сохранении заказа
                // Используем точное деление, чтобы избежать ошибок округления
                $priceAfterAdditionalDiscount = $totalPriceAfterDiscount / $quantity;
                
                // Общая скидка в процентах (средневзвешенная) для отображения
                $totalOriginalPrice = $priceBeforeAdditionalDiscount * $quantity;
                $totalDiscountAmount = $totalOriginalPrice - $totalPriceAfterDiscount;
                $discountPercent = round(($totalDiscountAmount / $totalOriginalPrice) * 100, 2);
                
                if ($hasDiscount) {
                    $basketItem['ADDITIONAL_DISCOUNT_PERCENT'] = $discountPercent;
                    $basketItem['ADDITIONAL_DISCOUNT_AMOUNT'] = $totalDiscountAmount / $quantity;
                    // Сохраняем информацию о скидках для каждой единицы
                    $basketItem['UNIT_PRICES_AFTER_DISCOUNT'] = $unitPricesAfterDiscount;
                }
            } else {
                // Одна единица - используем первый элемент
                $discountPercent = !empty($itemDiscounts) ? (reset($itemDiscounts) ?: 0) : 0;
                
                if ($discountPercent > 0) {
                    $discountAmount = $priceBeforeAdditionalDiscount * $discountPercent / 100;
                    $priceAfterAdditionalDiscount = $priceBeforeAdditionalDiscount - $discountAmount;
                    
                    $basketItem['ADDITIONAL_DISCOUNT_PERCENT'] = $discountPercent;
                    $basketItem['ADDITIONAL_DISCOUNT_AMOUNT'] = round($discountAmount);
                } else {
                    $priceAfterAdditionalDiscount = $priceBeforeAdditionalDiscount;
                }
            }

            // Сохраняем информацию о дополнительной скидке
            // Округляем скидочную цену до целого числа
            $basketItem['PRICE_BEFORE_ADDITIONAL_DISCOUNT'] = PriceMaths::roundPrecision($priceBeforeAdditionalDiscount);
            $basketItem['PRICE_AFTER_ADDITIONAL_DISCOUNT'] = round($priceAfterAdditionalDiscount);

            // Форматированные значения
            // В чекауте валюта может быть в разных полях
            $currency = $basketItem['CURRENCY'] ?? $basketItem['CURRENCY_ID'] ?? 'RUB';
            $basketItem['PRICE_BEFORE_ADDITIONAL_DISCOUNT_FORMATED'] = \CCurrencyLang::CurrencyFormat(
                $basketItem['PRICE_BEFORE_ADDITIONAL_DISCOUNT'],
                $currency,
                true
            );
            $basketItem['PRICE_AFTER_ADDITIONAL_DISCOUNT_FORMATED'] = \CCurrencyLang::CurrencyFormat(
                $basketItem['PRICE_AFTER_ADDITIONAL_DISCOUNT'],
                $currency,
                true
            );

            // НЕ обновляем основную PRICE - она должна оставаться как цена после первой скидки
            // Дополнительные цены будут использоваться только для отображения трех цен в шаблоне
            // Обновление PRICE на PRICE_AFTER_ADDITIONAL_DISCOUNT будет происходить только при сохранении заказа

            // Обновляем сумму после применения доп скидки
            // Для товаров с количеством > 1 и разными скидками на единицы
            // SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT должна быть точной суммой всех единиц со своими скидками
            $quantity = $basketItem['QUANTITY'] ?? 1;
            
            // Если есть UNIT_PRICES_AFTER_DISCOUNT (товар с количеством > 1 и разными скидками)
            // используем точную сумму, иначе рассчитываем как PRICE * QUANTITY
            if (!empty($basketItem['UNIT_PRICES_AFTER_DISCOUNT']) && is_array($basketItem['UNIT_PRICES_AFTER_DISCOUNT'])) {
                // Точная сумма всех единиц со своими скидками
                $basketItem['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT'] = array_sum($basketItem['UNIT_PRICES_AFTER_DISCOUNT']);
            } else {
                // Для товаров с одной скидкой на все единицы
                $basketItem['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT'] = round($basketItem['PRICE_AFTER_ADDITIONAL_DISCOUNT'] * $quantity);
            }
            
            $basketItem['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT_FORMATED'] = \CCurrencyLang::CurrencyFormat(
                $basketItem['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT'],
                $currency,
                true
            );

            // НЕ перезаписываем SUM_VALUE, SUM_NUM, SUM - они должны оставаться как цена после первой скидки
            // Это нужно для того, чтобы в чекауте отображалась цена после первой скидки, а не после дополнительной

            // Суммы для отображения 3 цен (ORIGINAL_PRICE уже установлен выше)
            $basketItem['SUM_PRICE_BEFORE_ADDITIONAL_DISCOUNT'] = $basketItem['PRICE_BEFORE_ADDITIONAL_DISCOUNT'] * $quantity;
            $basketItem['SUM_PRICE_BEFORE_ADDITIONAL_DISCOUNT_FORMATED'] = \CCurrencyLang::CurrencyFormat(
                $basketItem['SUM_PRICE_BEFORE_ADDITIONAL_DISCOUNT'],
                $currency,
                true
            );

            // Флаг для отображения 3 цен
            $basketItem['SHOW_THREE_PRICES'] = true;
        }
        unset($basketItem);

        return $basketItems;
    }

    /**
     * Добавить поля для отображения 3 цен
     *
     * @param array $basketItem Товар корзины
     * @return array Товар с дополнительными полями
     */
    protected static function addPriceFields(array $basketItem)
    {
        // В чекауте валюта может быть в разных полях
        $currency = $basketItem['CURRENCY'] ?? $basketItem['CURRENCY_ID'] ?? 'RUB';
        $quantity = $basketItem['QUANTITY'] ?? 1;

        // Цена без скидки (зачеркнутая) - используем BASE_PRICE как исходную цену БЕЗ всех скидок
        // Это должно быть установлено ДО вызова этого метода, но на всякий случай проверяем
        if (!isset($basketItem['ORIGINAL_PRICE'])) {
            // ORIGINAL_PRICE - это базовая цена БЕЗ всех скидок (BASE_PRICE)
            $originalPrice = $basketItem['BASE_PRICE'] ?? $basketItem['SUM_BASE'] / ($quantity > 0 ? $quantity : 1) ?? 0;
            $basketItem['ORIGINAL_PRICE'] = PriceMaths::roundPrecision($originalPrice);
            $basketItem['ORIGINAL_PRICE_FORMATED'] = \CCurrencyLang::CurrencyFormat(
                $basketItem['ORIGINAL_PRICE'],
                $currency,
                true
            );

            // Сумма без скидки
            if (!isset($basketItem['SUM_ORIGINAL_PRICE'])) {
                $basketItem['SUM_ORIGINAL_PRICE'] = $basketItem['ORIGINAL_PRICE'] * $quantity;
                $basketItem['SUM_ORIGINAL_PRICE_FORMATED'] = \CCurrencyLang::CurrencyFormat(
                    $basketItem['SUM_ORIGINAL_PRICE'],
                    $currency,
                    true
                );
            }
        }

        // Цена до применения доп скидки (если есть)
        if (!isset($basketItem['PRICE_BEFORE_ADDITIONAL_DISCOUNT'])) {
            $priceBeforeAdditionalDiscount = $basketItem['PRICE'] ?? $basketItem['BASE_PRICE'] ?? 0;
            $basketItem['PRICE_BEFORE_ADDITIONAL_DISCOUNT'] = PriceMaths::roundPrecision($priceBeforeAdditionalDiscount);
            $basketItem['PRICE_BEFORE_ADDITIONAL_DISCOUNT_FORMATED'] = \CCurrencyLang::CurrencyFormat(
                $basketItem['PRICE_BEFORE_ADDITIONAL_DISCOUNT'],
                $currency,
                true
            );

            // Сумма до применения доп скидки
            $basketItem['SUM_PRICE_BEFORE_ADDITIONAL_DISCOUNT'] = $basketItem['PRICE_BEFORE_ADDITIONAL_DISCOUNT'] * $quantity;
            $basketItem['SUM_PRICE_BEFORE_ADDITIONAL_DISCOUNT_FORMATED'] = \CCurrencyLang::CurrencyFormat(
                $basketItem['SUM_PRICE_BEFORE_ADDITIONAL_DISCOUNT'],
                $currency,
                true
            );
        }

        // Цена после применения доп скидки (текущая цена)
        if (!isset($basketItem['PRICE_AFTER_ADDITIONAL_DISCOUNT'])) {
            $priceAfterAdditionalDiscount = $basketItem['PRICE'] ?? $basketItem['BASE_PRICE'] ?? 0;
            $basketItem['PRICE_AFTER_ADDITIONAL_DISCOUNT'] = PriceMaths::roundPrecision($priceAfterAdditionalDiscount);
            $basketItem['PRICE_AFTER_ADDITIONAL_DISCOUNT_FORMATED'] = \CCurrencyLang::CurrencyFormat(
                $basketItem['PRICE_AFTER_ADDITIONAL_DISCOUNT'],
                $currency,
                true
            );

            // Сумма после применения доп скидки
            $basketItem['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT'] = $basketItem['PRICE_AFTER_ADDITIONAL_DISCOUNT'] * $quantity;
            $basketItem['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT_FORMATED'] = \CCurrencyLang::CurrencyFormat(
                $basketItem['SUM_PRICE_AFTER_ADDITIONAL_DISCOUNT'],
                $currency,
                true
            );
        }

        // Флаг для отображения 3 цен
        $basketItem['SHOW_THREE_PRICES'] = true;

        return $basketItem;
    }

    /**
     * Получить товары категории sale из массива товаров корзины
     *
     * @param array $basketItems Массив товаров корзины
     * @return array Массив товаров категории sale с информацией о цене и ID
     */
    protected static function getSaleItems(array $basketItems)
    {
        $saleItems = [];

        // Получаем ID всех товаров в корзине
        $productIds = [];
        $itemIdMap = [];

        foreach ($basketItems as $basketItem) {
            $productId = $basketItem['PRODUCT_ID'] ?? null;
            // Пробуем разные варианты идентификаторов
            $itemId = null;
            if (isset($basketItem['ID'])) {
                $itemId = $basketItem['ID'];
            } elseif (isset($basketItem['BASKET_ID'])) {
                $itemId = $basketItem['BASKET_ID'];
            } elseif (isset($basketItem['BASKET_CODE'])) {
                $itemId = $basketItem['BASKET_CODE'];
            }

            if (!$productId || !$itemId) {
                continue;
            }

            $productIds[] = $productId;
            $itemIdMap[$productId][] = [
                'item_id' => $itemId,
                'basket_item' => $basketItem,
            ];
        }

        if (empty($productIds)) {
            return [];
        }

        // Получаем информацию о товарах (через офферы)
        $offersResult = \CCatalogSKU::getProductList($productIds);
        $parentProductIds = [];

        foreach ($offersResult as $offerId => $product) {
            $parentProductIds[$offerId] = $product['ID'];
        }

        if (empty($parentProductIds)) {
            return [];
        }

        // Получаем значения OLD_PRICE для родительских товаров
        // Используем метод Product::getEcommerceData() как в других местах кода
        $parentIds = array_unique(array_values($parentProductIds));

        // Используем Product::getEcommerceData() для получения oldPrice
        $product = new \Level44\Product();
        $ecommerceData = $product->getEcommerceData($parentIds);

        $saleProductIds = [];
        foreach ($ecommerceData as $productId => $data) {
            if (!empty($data['prices']['oldPrice']) && (float)$data['prices']['oldPrice'] > 0) {
                $saleProductIds[] = $productId;
            }
        }

        // Формируем список товаров категории sale
        foreach ($basketItems as $basketItem) {
            $productId = $basketItem['PRODUCT_ID'] ?? null;
            // Пробуем разные варианты идентификаторов
            $itemId = null;
            if (isset($basketItem['ID'])) {
                $itemId = $basketItem['ID'];
            } elseif (isset($basketItem['BASKET_ID'])) {
                $itemId = $basketItem['BASKET_ID'];
            } elseif (isset($basketItem['BASKET_CODE'])) {
                $itemId = $basketItem['BASKET_CODE'];
            }

            if (!$productId || !$itemId) {
                continue;
            }

            $parentProductId = $parentProductIds[$productId] ?? null;

            if ($parentProductId && in_array($parentProductId, $saleProductIds)) {
                // Используем BASE_PRICE для расчета скидок (цена до применения базовых скидок)
                // Это важно, так как дополнительные скидки применяются к цене после базовых скидок
                $price = $basketItem['BASE_PRICE'] ?? $basketItem['PRICE'] ?? 0;
                $quantity = $basketItem['QUANTITY'] ?? 1;
                
                // Если товар в количестве > 1, добавляем каждую единицу отдельно для расчета скидок
                // Это позволяет применять скидки к каждой единице по правилам (0%, 10%, 15%, 20%, 20%...)
                for ($i = 0; $i < $quantity; $i++) {
                    $saleItems[] = [
                        'item_id' => $itemId,
                        'product_id' => $productId,
                        'price' => $price,
                        'quantity_index' => $i, // Индекс единицы (0, 1, 2, ...)
                    ];
                }
            }
        }

        return $saleItems;
    }

    /**
     * Рассчитать скидки для товаров категории sale
     *
     * @param array $saleItems Массив товаров категории sale
     * @param int $count Количество товаров
     * @return array Массив скидок [item_id => discount_percent]
     */
    protected static function calculateDiscounts(array $saleItems, int $count)
    {
        $discounts = [];

        if ($count === 1) {
            // 1 товар - нет доп скидки
            return $discounts;
        }

        // Сортируем товары по цене (от меньшей к большей)
        usort($saleItems, function($a, $b) {
            return $a['price'] <=> $b['price'];
        });

        if ($count === 2) {
            // 2 товара: на товар наименьшей стоимости - 10% скидка
            $itemId = $saleItems[0]['item_id'];
            $qtyIndex = $saleItems[0]['quantity_index'] ?? 0;
            $discounts[$itemId . '_qty_' . $qtyIndex] = 10;
        } elseif ($count === 3) {
            // 3 товара: 1 товар (наименьший) - 15%, 2 товар - 10%, 3 товар - нет доп скидки
            $itemId1 = $saleItems[0]['item_id'];
            $qtyIndex1 = $saleItems[0]['quantity_index'] ?? 0;
            $discounts[$itemId1 . '_qty_' . $qtyIndex1] = 15;
            
            $itemId2 = $saleItems[1]['item_id'];
            $qtyIndex2 = $saleItems[1]['quantity_index'] ?? 0;
            $discounts[$itemId2 . '_qty_' . $qtyIndex2] = 10;
            // Третий товар без доп скидки
        } else {
            // 4+ товара: скидки распределяются от дорогих к дешевым
            // Самый дорогой (последний) - 0%, предпоследний - 10%, третий с конца - 15%, остальные (более дешевые) - 20%
            // Пример для 5 товаров: 0%, 10%, 15%, 20%, 20% (от дорогого к дешевому)
            // Пример для 6 товаров: 0%, 10%, 15%, 20%, 20%, 20% (от дорогого к дешевому)
            
            $lastIndex = $count - 1;
            
            // Самый дорогой товар (последний) - без скидки (0%)
            // Не добавляем в $discounts, так как 0% означает отсутствие дополнительной скидки
            
            // Предпоследний товар - 10%
            if ($count >= 2) {
                $itemId = $saleItems[$lastIndex - 1]['item_id'];
                $qtyIndex = $saleItems[$lastIndex - 1]['quantity_index'] ?? 0;
                $discounts[$itemId . '_qty_' . $qtyIndex] = 10;
            }
            
            // Третий с конца - 15%
            if ($count >= 3) {
                $itemId = $saleItems[$lastIndex - 2]['item_id'];
                $qtyIndex = $saleItems[$lastIndex - 2]['quantity_index'] ?? 0;
                $discounts[$itemId . '_qty_' . $qtyIndex] = 15;
            }
            
            // Все остальные товары (более дешевые) - 20%
            // Это товары с индексами от 0 до (count - 4) включительно
            for ($i = 0; $i <= $lastIndex - 3; $i++) {
                $itemId = $saleItems[$i]['item_id'];
                $qtyIndex = $saleItems[$i]['quantity_index'] ?? 0;
                $discounts[$itemId . '_qty_' . $qtyIndex] = 20;
            }
        }

        return $discounts;
    }

    /**
     * Регистрация обработчиков событий
     */
    public static function register()
    {
        AddEventHandler('sale', 'OnSaleComponentOrderCreated', [self::class, 'OnSaleComponentOrderCreatedHandler']);
        static::addEventHandler('sale', 'OnSaleOrderBeforeSaved');
        static::addEventHandler('sale', 'OnSaleOrderSaved');
    }

    /**
     * Обработчик события создания заказа - применяет дополнительные скидки к товарам корзины
     *
     * @param Order $order Объект заказа
     * @param array $arUserResult Данные пользователя
     * @param \Bitrix\Main\HttpRequest $request Запрос
     * @param array $arParams Параметры компонента
     * @param array $arResult Результат компонента
     * @param array $arDeliveryServiceAll Службы доставки
     * @param array $arPaySystemServiceAll Платежные системы
     */
    public static function OnSaleComponentOrderCreatedHandler(
        Order $order,
        &$arUserResult,
        $request,
        &$arParams,
        &$arResult,
        &$arDeliveryServiceAll,
        &$arPaySystemServiceAll
    ) {
        if (!Loader::includeModule('sale') || !Loader::includeModule('iblock')) {
            return;
        }

        $basket = $order->getBasket();
        if (!$basket || $basket->isEmpty()) {
            return;
        }

        // Получаем товары корзины в виде массива для обработки
        $basketItemsArray = [];
        /** @var \Bitrix\Sale\BasketItem $basketItem */
        foreach ($basket as $basketItem) {
            $basketItemsArray[] = [
                'ID' => $basketItem->getBasketCode(),
                'PRODUCT_ID' => $basketItem->getProductId(),
                'PRICE' => $basketItem->getPrice(),
                'BASE_PRICE' => $basketItem->getBasePrice(),
                'QUANTITY' => $basketItem->getQuantity(),
                'CURRENCY' => $basketItem->getCurrency(),
                'CURRENCY_ID' => $basketItem->getCurrency(),
            ];
        }

        // Применяем дополнительные скидки
        $basketItemsArray = self::applyDiscounts($basketItemsArray);

        // Применяем скидки к товарам корзины в заказе
        $itemsUpdated = 0;
        foreach ($basketItemsArray as $itemData) {
            if (empty($itemData['ADDITIONAL_DISCOUNT_PERCENT']) || $itemData['ADDITIONAL_DISCOUNT_PERCENT'] <= 0) {
                continue;
            }

            /** @var \Bitrix\Sale\BasketItem $basketItem */
            $basketItem = $basket->getItemByBasketCode($itemData['ID']);
            if (!$basketItem) {
                continue;
            }

            // Применяем дополнительную скидку к цене товара
            // Для товаров с количеством > 1 и разными скидками на единицы
            // используем цену за единицу, которая при умножении на количество даст точную сумму всех единиц
            $quantity = $basketItem->getQuantity();
            
            if (!empty($itemData['UNIT_PRICES_AFTER_DISCOUNT']) && is_array($itemData['UNIT_PRICES_AFTER_DISCOUNT']) && $quantity > 1) {
                // Точная сумма всех единиц со своими скидками
                $totalPriceAfterDiscount = array_sum($itemData['UNIT_PRICES_AFTER_DISCOUNT']);
                // Цена за единицу, которая даст правильную сумму при умножении на количество
                $newPrice = $totalPriceAfterDiscount / $quantity;
            } else {
                $newPrice = $itemData['PRICE_AFTER_ADDITIONAL_DISCOUNT'] ?? $basketItem->getPrice();
                $newPrice = round($newPrice);
            }

            // Устанавливаем кастомную цену, чтобы система не пересчитывала её
            $basketItem->setFieldNoDemand('CUSTOM_PRICE', 'Y');
            $basketItem->setFieldNoDemand('PRICE', $newPrice);

            // Пересчитываем DISCOUNT_PRICE
            $basePrice = $basketItem->getBasePrice();
            $discountPrice = $basePrice - $newPrice;
            if ($discountPrice > 0) {
                $basketItem->setFieldNoDemand('DISCOUNT_PRICE', $discountPrice);
            }

            $itemsUpdated++;
        }

        // Пересчитываем заказ после применения скидок
        $order->doFinalAction(true);
    }

    /**
     * Обработчик события перед сохранением заказа - применяет дополнительные скидки
     *
     * @param \Bitrix\Main\Event $event Событие
     * @return \Bitrix\Main\EventResult
     */
    public static function OnSaleOrderBeforeSavedHandler(\Bitrix\Main\Event $event)
    {
        /** @var Order $order */
        $order = $event->getParameter('ENTITY');

        if (!$order || !($order instanceof Order)) {
            return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS);
        }

        if (!Loader::includeModule('sale') || !Loader::includeModule('iblock')) {
            return;
        }

        $basket = $order->getBasket();
        if (!$basket || $basket->isEmpty()) {
            return;
        }

        // Получаем товары корзины в виде массива для обработки
        $basketItemsArray = [];
        /** @var \Bitrix\Sale\BasketItem $basketItem */
        foreach ($basket as $basketItem) {
            $basketCode = $basketItem->getBasketCode();
            $basketItemsArray[] = [
                'ID' => $basketCode,
                'BASKET_CODE' => $basketCode,
                'PRODUCT_ID' => $basketItem->getProductId(),
                'PRICE' => $basketItem->getPrice(),
                'BASE_PRICE' => $basketItem->getBasePrice(),
                'QUANTITY' => $basketItem->getQuantity(),
                'CURRENCY' => $basketItem->getCurrency(),
                'CURRENCY_ID' => $basketItem->getCurrency(),
            ];
        }

        // Применяем дополнительные скидки
        $basketItemsArray = self::applyDiscounts($basketItemsArray);

        // Применяем скидки к товарам корзины в заказе
        $itemsUpdated = 0;
        foreach ($basketItemsArray as $itemData) {
            if (empty($itemData['ADDITIONAL_DISCOUNT_PERCENT']) || $itemData['ADDITIONAL_DISCOUNT_PERCENT'] <= 0) {
                continue;
            }

            // Пробуем найти товар по разным идентификаторам
            $basketCode = $itemData['BASKET_CODE'] ?? $itemData['ID'] ?? null;
            /** @var \Bitrix\Sale\BasketItem $basketItem */
            $basketItem = null;

            if ($basketCode) {
                $basketItem = $basket->getItemByBasketCode($basketCode);
            }

            if (!$basketItem) {
                // Пробуем найти по PRODUCT_ID
                foreach ($basket as $item) {
                    if ($item->getProductId() == ($itemData['PRODUCT_ID'] ?? null)) {
                        $basketItem = $item;
                        break;
                    }
                }
            }

            if (!$basketItem) {
                continue;
            }

            // Применяем дополнительную скидку к цене товара
            // Для товаров с количеством > 1 и разными скидками на единицы
            // используем цену за единицу, которая при умножении на количество даст точную сумму всех единиц
            $quantity = $basketItem->getQuantity();
            
            if (!empty($itemData['UNIT_PRICES_AFTER_DISCOUNT']) && is_array($itemData['UNIT_PRICES_AFTER_DISCOUNT']) && $quantity > 1) {
                // Точная сумма всех единиц со своими скидками
                $totalPriceAfterDiscount = array_sum($itemData['UNIT_PRICES_AFTER_DISCOUNT']);
                // Цена за единицу, которая даст правильную сумму при умножении на количество
                $newPrice = $totalPriceAfterDiscount / $quantity;
            } else {
                $newPrice = $itemData['PRICE_AFTER_ADDITIONAL_DISCOUNT'] ?? $basketItem->getPrice();
                $newPrice = round($newPrice);
            }

            // Устанавливаем кастомную цену, чтобы система не пересчитывала её
            $basketItem->setFieldNoDemand('CUSTOM_PRICE', 'Y');
            $basketItem->setFieldNoDemand('PRICE', $newPrice);

            // Пересчитываем DISCOUNT_PRICE
            $basePrice = $basketItem->getBasePrice();
            $discountPrice = $basePrice - $newPrice;
            if ($discountPrice > 0) {
                $basketItem->setFieldNoDemand('DISCOUNT_PRICE', $discountPrice);
            }

            $itemsUpdated++;
        }

        // Возвращаем результат события
        $parameters = [
            "ENTITY" => $order,
        ];

        return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS, $parameters);
    }

    /**
     * Обработчик события после сохранения заказа - обновляет цены через SQL
     *
     * @param \Bitrix\Main\Event $event Событие
     * @return \Bitrix\Main\EventResult
     */
    public static function OnSaleOrderSavedHandler(\Bitrix\Main\Event $event)
    {
        /** @var Order $order */
        $order = $event->getParameter('ENTITY');
        $isNew = $event->getParameter('IS_NEW');

        if (!$order || !($order instanceof Order) || !$isNew) {
            return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS);
        }

        if (!Loader::includeModule('sale') || !Loader::includeModule('iblock')) {
            return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS);
        }

        $basket = $order->getBasket();
        if (!$basket || $basket->isEmpty()) {
            return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS);
        }

        $orderId = $order->getId();

        // Получаем товары корзины в виде массива для обработки
        $basketItemsArray = [];
        $basketItemIds = []; // Сохраняем ID товаров корзины для SQL обновления
        /** @var \Bitrix\Sale\BasketItem $basketItem */
        foreach ($basket as $basketItem) {
            $basketCode = $basketItem->getBasketCode();
            $basketItemId = $basketItem->getId(); // ID товара корзины в БД
            $currentPrice = $basketItem->getPrice();
            $basePrice = $basketItem->getBasePrice();

            $basketItemsArray[] = [
                'ID' => $basketCode,
                'BASKET_CODE' => $basketCode,
                'BASKET_ITEM_ID' => $basketItemId, // ID в таблице b_sale_basket
                'PRODUCT_ID' => $basketItem->getProductId(),
                'PRICE' => $currentPrice,
                'BASE_PRICE' => $basePrice,
                'QUANTITY' => $basketItem->getQuantity(),
                'CURRENCY' => $basketItem->getCurrency(),
            ];
        }

        // Применяем дополнительные скидки
        $basketItemsArray = self::applyDiscounts($basketItemsArray);

        // Обновляем цены через прямой SQL запрос
        $itemsUpdated = 0;
        foreach ($basketItemsArray as $itemData) {
            if (empty($itemData['ADDITIONAL_DISCOUNT_PERCENT']) || $itemData['ADDITIONAL_DISCOUNT_PERCENT'] <= 0) {
                continue;
            }

            // Используем PRICE_AFTER_ADDITIONAL_DISCOUNT из applyDiscounts
            // Для товаров с количеством > 1 и разными скидками на единицы
            // используем цену за единицу, которая при умножении на количество даст точную сумму всех единиц
            $quantity = $itemData['QUANTITY'] ?? 1;
            
            if (!empty($itemData['UNIT_PRICES_AFTER_DISCOUNT']) && is_array($itemData['UNIT_PRICES_AFTER_DISCOUNT']) && $quantity > 1) {
                // Точная сумма всех единиц со своими скидками
                $totalPriceAfterDiscount = array_sum($itemData['UNIT_PRICES_AFTER_DISCOUNT']);
                // Цена за единицу, которая даст правильную сумму при умножении на количество
                $newPrice = $totalPriceAfterDiscount / $quantity;
            } else {
                $newPrice = $itemData['PRICE_AFTER_ADDITIONAL_DISCOUNT'] ?? 0;
                $newPrice = round($newPrice);
            }
            
            if ($newPrice <= 0) {
                continue;
            }

            $basketItemId = $itemData['BASKET_ITEM_ID'] ?? null;
            $productId = $itemData['PRODUCT_ID'] ?? null;
            $basePrice = $itemData['BASE_PRICE'] ?? $newPrice;

            if (!$basketItemId && !$productId) {
                continue;
            }

            // Обновляем цену через SQL
            $connection = \Bitrix\Main\Application::getConnection();
            $sqlHelper = $connection->getSqlHelper();

            // DISCOUNT_PRICE = BASE_PRICE - newPrice (общая скидка от базовой цены)
            $discountPrice = max(0, $basePrice - $newPrice);

            $newPriceEscaped = $sqlHelper->forSql($newPrice);
            $discountPriceEscaped = $sqlHelper->forSql($discountPrice);
            $orderIdEscaped = $sqlHelper->forSql($orderId);

            // Обновляем цену товара в корзине заказа
            // Используем ID товара корзины (поле ID в таблице b_sale_basket)
            if ($basketItemId) {
                $basketItemIdEscaped = $sqlHelper->forSql($basketItemId);
                $sql = "UPDATE b_sale_basket 
                        SET PRICE = {$newPriceEscaped}, 
                            CUSTOM_PRICE = 'Y',
                            DISCOUNT_PRICE = {$discountPriceEscaped}
                        WHERE ID = {$basketItemIdEscaped} 
                        AND ORDER_ID = {$orderIdEscaped}";
            } else {
                // Если ID нет, используем PRODUCT_ID и ORDER_ID
                $productIdEscaped = $sqlHelper->forSql($productId);
                $sql = "UPDATE b_sale_basket 
                        SET PRICE = {$newPriceEscaped}, 
                            CUSTOM_PRICE = 'Y',
                            DISCOUNT_PRICE = {$discountPriceEscaped}
                        WHERE PRODUCT_ID = {$productIdEscaped} 
                        AND ORDER_ID = {$orderIdEscaped}";
            }

            $connection->queryExecute($sql);

            if ($connection->getAffectedRowsCount() > 0) {
                $itemsUpdated++;
            }
        }

        // НЕ пересчитываем заказ через doFinalAction, так как это перезапишет наши кастомные цены
        // Цены уже установлены через SQL с CUSTOM_PRICE='Y', что должно предотвратить пересчет

        return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS);
    }
}

