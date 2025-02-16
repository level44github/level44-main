<?php
/**
 * Created by PhpStorm.
 * User: Hozberg
 * Date: 30.04.21
 * Time: 11:03
 */

namespace Level44;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Catalog\Model;
use Exception;

/**
 * Class Product
 * @package Level44
 */
class Product
{
    /**
     * Product constructor.
     * @throws Exception
     * @throws LoaderException
     */
    public function __construct()
    {
        if (!Loader::includeModule('catalog')) {
            throw new Exception('Не подключен модуль catalog');
        }
    }


    /**
     * @param array $productsId
     * @param int $iblockId
     * @return array
     * @throws ArgumentException
     * @throws Exception
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getEcommerceData($productsId): array
    {
        if (empty($productsId)) {
            return [];
        }

        $data = [];
        $result = \CIBlockElement::GetList(
            [],
            [
                "ID" => $productsId,
            ],
            false,
            false,
            [
                "IBLOCK_ID",
                "ID",
                "PROPERTY_OLD_PRICE",
                "PROPERTY_OLD_PRICE_DOLLAR"
            ]
        );
        $productsData = [];
        while ($row = $result->GetNext()) {
            $productsData[$row["ID"]] = [
                "oldPrice"       => $row["PROPERTY_OLD_PRICE_VALUE"],
                "oldPriceDollar" => $row["PROPERTY_OLD_PRICE_DOLLAR_VALUE"],
            ];
        }

        $products = Model\Product::getList(
            [
                "filter" => [
                    "ID" => $productsId,
                ],
            ]
        );

        while ($product = $products->fetch()) {
            if (!empty($productsData[$product["ID"]])) {
                $productsData[$product["ID"]]["quantity"] = (int)$product["QUANTITY"];
            }
        }

        foreach ($productsId as $productId) {
            $productData = $productsData[$productId];
            $prices = [
                'oldPrice'       => (int)$productData["oldPrice"],
                'oldPriceFormat' => \CCurrencyLang::CurrencyFormat($productData["oldPrice"], "RUB"),
            ];

            $prices["oldPriceDollar"] = Base::getDollarPrice(
                $prices["oldPrice"],
                $productData["oldPriceDollar"],
                true
            );

            $prices["oldPriceDollarFormat"] = Base::getDollarPrice(
                $prices["oldPrice"],
                $productData["oldPriceDollar"]
            );
            $data[$productId]["prices"] = $prices;
            $data[$productId]["quantity"] = $productData["quantity"];
        }

        return $data;
    }
}