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
        }

        return $data;
    }
}