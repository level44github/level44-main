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

        return array_map(function ($item) {
            if ($item[3]['CODE'] === 'skoro_v_prodazhe') {
                $item[3]['IS_COMING_SOON'] = true;
            }

            return $item;
        }, $tree);
    }

    static function addSaleSection($menuSections): array
    {
        $comingSoonIndex = array_search(true, array_map(fn($item) => $item[3]["IS_COMING_SOON"], $menuSections));

        if (static::existSaleProducts()) {
            $saleItem = [
                "Sale",
                SITE_DIR . "catalog/sale/",
                [],
                [
                    "CSS_CLASS" => "sale-section",
                    "IS_SALE"   => true,
                    "CHILDREN"  => static::getSaleChildren()
                ],
                ""
            ];

            array_splice($menuSections, $comingSoonIndex + 1, 0, [$saleItem]);
        }

        return $menuSections;
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

    public static function getSaleChildren(): array
    {
        global $APPLICATION;

        if (\CModule::IncludeModule('iblock')) {
            $arFilter = [
                "TYPE"    => "catalog",
                "SITE_ID" => SITE_ID,
            ];

            $dbIBlock = \CIBlock::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], $arFilter);
            $dbIBlock = new \CIBlockResult($dbIBlock);

            if ($arIBlock = $dbIBlock->GetNext()) {
                if ($arIBlock["ACTIVE"] == "Y") {
                    $aMenuLinksExt = $APPLICATION->IncludeComponent(
                        "level44:menu.sections",
                        "",
                        [
                            "IS_SEF"           => "Y",
                            "SEF_BASE_URL"     => "",
                            "SECTION_PAGE_URL" => str_replace('/catalog/', '/catalog/sale/', $arIBlock['SECTION_PAGE_URL']),
                            "DETAIL_PAGE_URL"  => $arIBlock['DETAIL_PAGE_URL'],
                            "IBLOCK_TYPE"      => $arIBlock['IBLOCK_TYPE_ID'],
                            "IBLOCK_ID"        => $arIBlock['ID'],
                            "DEPTH_LEVEL"      => "3",
                            "CACHE_TYPE"       => "N",
                            "SALE_FILTER"      => "Y",
                        ],
                        false,
                        ['HIDE_ICONS' => 'Y']
                    );
                }
            }
        }

        return static::prepareMenuSections($aMenuLinksExt);
    }
}