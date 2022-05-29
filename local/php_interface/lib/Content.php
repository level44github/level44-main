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
use Bitrix\Main\Type\Collection;

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

    /**
     * @param int $productId
     * @return array
     */
    public static function recommendProductsOrdered(int $productId)
    {
        //Получаем привязки свойства "Другие товары из комплекта"
        $result = \CIBlockElement::GetProperty(Base::CATALOG_IBLOCK_ID, $productId, [], ["CODE" => "OTHER_KIT_ITEMS"]);

        $resultList = [];

        while ($row = $result->GetNext()) {
            //Исключаем текущий товар
            if ((int)$row["ID"] !== (int)$productId) {
                $resultList[] = (int)$row["VALUE"];
            }
        }

        //Получаем все разделы текущего товара
        $result = \CIBlockElement::GetElementGroups($productId, true, ["ID", "SORT"]);

        $sections = [];
        while ($row = $result->GetNext()) {
            $sections[$row["ID"]] = $row;
        }

        //Сортируем разделы в соответствии с индексом сортировки
        Collection::sortByColumn($sections, ["SORT" => SORT_ASC], "", null, true);

        //Получаем все товары из данных разделов
        $result = \CIBlockElement::GetList(
            [],
            [
                "IBLOCK_ID"  => Base::CATALOG_IBLOCK_ID,
                "SECTION_ID" => array_keys($sections),
                "=AVAILABLE" => "Y",
                "ACTIVE"     => "Y",
            ],
            false,
            false,
            [
                "ID",
                "IBLOCK_ID",
                "SORT",
            ]);

        $productsForSections = [];
        while ($row = $result->GetNext()) {
            //Исключаем текущий товар
            if ((int)$row["ID"] !== (int)$productId) {
                $productsForSections[$row["ID"]] = $row;
            }
        }

        //Получаем все разделы для товаров из этих разделов
        //(Что бы разместить товар в разделе с наименьшим индексом сортировки)
        $result = \CIBlockElement::GetElementGroups(array_keys($productsForSections), true);

        while ($row = $result->GetNext()) {
            if (array_key_exists($row["ID"], $sections)) {
                //Раскидываем товары по разделам
                $sections[$row["ID"]]["PRODUCTS"][] = [
                    "ID"   => $row["IBLOCK_ELEMENT_ID"],
                    "SORT" => $productsForSections[$row["IBLOCK_ELEMENT_ID"]]["SORT"],
                ];
            }
        }

        //Внутри каждого раздела, сортируем товары в соответствии с сортировкой в каталоге
        foreach ($sections as &$section) {
            Collection::sortByColumn($section["PRODUCTS"], ['SORT' => SORT_ASC, 'ID' => SORT_DESC]);
        }
        unset($section);


        do {
            $commonCount = 0;

            foreach ($sections as &$section) {
                $product = array_shift($section["PRODUCTS"]);

                if ($product["ID"] && !in_array($product["ID"], $resultList)) {
                    $resultList[] = (int)$product["ID"];
                }

                $commonCount += count($section["PRODUCTS"]);
            }
            unset($section);

        } while ($commonCount > 0);

        //Удаляем пустые значения
        $resultList = array_values(array_filter($resultList));

        return $resultList;
    }
}