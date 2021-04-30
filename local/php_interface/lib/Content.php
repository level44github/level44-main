<?php
/**
 * Created by PhpStorm.
 * User: Hozberg
 * Date: 30.04.21
 * Time: 11:00
 */

namespace Level44;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

/**
 * Class Content
 * @package Level44
 */
class Content
{

    /**
     * @param array $arResult
     * @return array
     * @throws ArgumentException
     * @throws \Exception
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function setCatalogItemsEcommerceData($arResult): array
    {
        $productsId = [];
        foreach ($arResult['ITEMS'] as $itemNum => $item) {
            if (in_array((int)$item['ID'], $productsId, true)) {
                continue;
            }
            $productsId[] = (int)$item['ID'];
        }

        if (!empty($productsId)) {
            $product = new Product;
            $ecommerceData = $product->getEcommerceData($productsId, $arResult['IBLOCK_ID']);
        }

        foreach ($arResult['ITEMS'] as $itemNum => $item) {
            if (empty($ecommerceData[$item['ID']])) {
                continue;
            }

            $item['ecommerceData'] = $ecommerceData[$item['ID']];
            $arResult['ITEMS'][$itemNum] = $item;
        }

        return $arResult;
    }
}