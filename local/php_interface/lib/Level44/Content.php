<?php
/**
 * Created by PhpStorm.
 * User: Hozberg
 * Date: 30.04.21
 * Time: 11:00
 */

namespace Level44;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
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
    private $sortData = [];

    public function __construct($section)
    {
        $this->initSortData($section);
    }

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
            if ((int)$row["VALUE"] !== $productId) {
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

        $sections = array_filter($sections, fn($section) => is_array($section["PRODUCTS"]));

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
        return array_values(array_filter($resultList));
    }

    /**
     * @param int $productId
     * @return array<int>
     */
    public static function getAddToYouLookProducts(int $productId): array
    {
        $resultList = [];
        $productsId = [];
        $propertyResult = \CIBlockElement::GetProperty(Base::CATALOG_IBLOCK_ID, $productId, [], ["CODE" => "ADD_TO_YOUR_LOOK"]);

        while ($row = $propertyResult->GetNext()) {
            if ($row["VALUE"] && (int)$row["VALUE"] !== $productId) {
                $productsId[] = (int)$row["VALUE"];
            }
        }

        if (!empty($productsId)) {
            $result = \CIBlockElement::GetList(
                ['ID' => $productsId],
                [
                    "IBLOCK_ID"  => Base::CATALOG_IBLOCK_ID,
                    "=ID"        => $productsId,
                    "=AVAILABLE" => "Y",
                    "ACTIVE"     => "Y",
                ],
                false,
                false,
                [
                    "ID",
                    "IBLOCK_ID",
                ]);

            while ($row = $result->GetNext()) {
                $resultList[] = (int)$row["ID"];
            }
        }

        return $resultList;
    }

    /**
     * @return void
     */
    private function initSortData($section): void
    {
        $request = Context::getCurrent()->getRequest();
        $sortData =& $this->sortData;
        $sortData["code"] = $request->getCookie("sort".$section);
        $sortData["field2"] = 'sort';
        $sortData["order2"] = 'asc';

        switch ($sortData["code"]) {
            case "price-asc":
                $sortData["field"] = 'SCALED_PRICE_1';
                $sortData["order"] = 'asc';
                break;
            case "price-desc":
                $sortData["field"] = 'SCALED_PRICE_1';
                $sortData["order"] = 'desc';
                break;
            case "popularity":
                $sortData["field"] = 'shows';
                $sortData["order"] = 'desc';
                break;
            case "new":
                $sortData["field"] = 'created';
                $sortData["order"] = 'desc';
                break;
            default:
                $sortData["field"] = 'sort';
                $sortData["order"] = 'asc';
                $sortData["code"] = 'default';
                break;
        }

        $sortData["list"] = [
            [
                "code" => "default",
                "name" => Base::getMultiLang("По умолчанию", "Default"),
            ],
            [
                "code" => "price-asc",
                "name" => Base::getMultiLang("По возрастанию цены", "Price: Low to High"),
            ],
            [
                "code" => "price-desc",
                "name" => Base::getMultiLang("По убыванию цены", "Price: High to Low"),
            ],
            [
                "code" => "popularity",
                "name" => Base::getMultiLang("По популярности", "Popularity"),
            ],
            [
                "code" => "new",
                "name" => Base::getMultiLang("По новизне", "Newest"),
            ],
        ];

        if (!in_array($sortData["code"], array_map(fn($item) => $item['code'], $sortData["list"]))) {
            $sortData["code"] = 'default';
        }

        $sortData["list"] = array_map(
            fn($item) => array_merge($item, ['selected' => $item["code"] === $sortData["code"]]),
            $sortData["list"]
        );


        unset($sortData);
    }

    /**
     * @param string $fieldName
     * @return string
     */
    public function getSortValue(string $fieldName): string
    {
        return $this->sortData[$fieldName] ?: '';
    }

    /**
     * @return array
     */
    public function getSortList(): array
    {
        return $this->sortData["list"];
    }
}