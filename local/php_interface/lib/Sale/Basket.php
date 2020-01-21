<?php

namespace Level44\Sale;

use \Bitrix\Sale;
use \Bitrix\Sale\BasketItem;
use \Bitrix\Main;
use \Bitrix\Main\Localization\Loc;


class Basket extends Sale\Basket
{
    /**
     * @return array|bool
     * @throws Main\ArgumentException
     * @throws Main\ArgumentNullException
     * @throws Main\NotImplementedException
     */
    // must be moved to notify
    public function getListOfFormatText()
    {
        $list = array();

        /** @var BasketItem $basketItemClassName */
        $basketItemClassName = static::getItemCollectionClassName();

        /** @var BasketItem $basketItem */
        foreach ($this->collection as $basketItem) {
            $basketProductIds[] = (int)$basketItem->getField("PRODUCT_ID");
        }

        $arProductsLoc = [];

        if (!empty($basketProductIds)) {
            $resProduct = \CIBlockElement::GetList(
                [],
                [
                    "=ID" => $basketProductIds
                ],
                false,
                false,
                [
                    "ID",
                    "PROPERTY_NAME_EN"
                ]
            );


            while ($product = $resProduct->GetNext()) {
                $arProductsLoc[$product["ID"]]["NAME_EN"] = $product["PROPERTY_NAME_EN_VALUE"];
            }
        }

        /** @var BasketItem $basketItem */
        foreach ($this->collection as $basketItem) {
            $productId = $basketItem->getField("PRODUCT_ID");
            $basketItemData = \Level44\Base::isEnLang() && !empty($arProductsLoc[$productId]["NAME_EN"])
                ? $arProductsLoc[$productId]["NAME_EN"] : $basketItem->getField("NAME");

            $measure = (strval($basketItem->getField("MEASURE_NAME")) != '') ? $basketItem->getField("MEASURE_NAME") : Loc::getMessage("SOA_SHT");
            $list[$basketItem->getBasketCode()] = $basketItemData . " - " . $basketItemClassName::formatQuantity($basketItem->getQuantity()) . " " . $measure . " x " . SaleFormatCurrency($basketItem->getPrice(),
                    $basketItem->getCurrency());

        }

        return !empty($list) ? $list : false;
    }
}
