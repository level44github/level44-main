<?php

namespace Level44;

class Menu
{
    static function prepareMenuSections($sections): array
    {
        $tree = $parents = [];

        foreach ($sections as &$section) {
            $section[3]['CHILDREN'] = [];
            if (isset($parents[$section[3]['DEPTH_LEVEL']])) {
                unset($parents[$section[3]['DEPTH_LEVEL']]);
            }
            $parents[$section[3]['DEPTH_LEVEL']] = &$section;
            if ($section[3]['DEPTH_LEVEL'] > 1) {
                $parents[$section[3]['DEPTH_LEVEL'] - 1][3]['CHILDREN'][] = &$section;
            } else {
                $tree[] = &$section;
            }
        }
        unset($section);

        $tree = array_map(function ($item) {
            switch ($item[3]['CODE']) {
                case 'skoro_v_prodazhe':
                    $item[3]['IS_COMING_SOON'] = true;
                    unset($item[3]['CHILDREN']);
                    break;
                case 'novinki':
                    $item[3]['IS_NEW'] = true;
                    unset($item[3]['CHILDREN']);
                    break;
                case 'sale':
                    $item[3]['CSS_CLASS'] = 'sale-section';
                    $item[3]['IS_SALE'] = true;
                    break;
            }

            return $item;
        }, $tree);

        $comingSoonIndex = array_search(true, array_map(fn($item) => $item[3]["IS_COMING_SOON"], $tree));

        if (static::existSaleProducts()) {
            $saleItem = [
                "Sale",
                SITE_DIR . "catalog/sale/",
                [],
                [
                    "CSS_CLASS" => "sale-section",
                    "IS_SALE"   => true
                ],
                ""
            ];

            array_splice($tree, $comingSoonIndex + 1, 0, [$saleItem]);
        }

        return $tree;
    }


    /**
     * @return bool
     */
    public static function existSaleProducts(): bool
    {
        $productsId = [];
        $exist = false;
        $result = \CIBlockElement::GetList(
            [],
            [
                "ACTIVE"              => "Y",
                "IBLOCK_ID"           => Base::CATALOG_IBLOCK_ID,
                ">PROPERTY_OLD_PRICE" => 0,
            ],
            false,
            false,
            [
                "IBLOCK_ID",
                "ID",
                "PROPERTY_OLD_PRICE"
            ]
        );

        while ($row = $result->GetNext()) {
            $productsId[] = $row["ID"];
        }

        if (!empty($productsId)) {
            $result = \CIBlockElement::GetList(
                [],
                [
                    "ACTIVE"             => "Y",
                    "CATALOG_AVAILABLE"  => "Y",
                    "IBLOCK_ID"          => Base::OFFERS_IBLOCK_ID,
                    "PROPERTY_CML2_LINK" => $productsId,
                ],
                false,
                [
                    "nTopCount" => 1
                ]
            )->GetNext();

            $exist = !empty($result);
        }
        return $exist;
    }
}