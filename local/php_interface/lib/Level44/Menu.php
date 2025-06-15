<?php

namespace Level44;


class Menu
{
    static function prepareMenuSections($sections): array
    {
        $sections = array_map(fn($section) => static::markIfSelected($section), $sections);

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

        return $tree;
    }

    static function addSaleSection($menuSections): array
    {
        $saleChildren = static::getSaleChildren();

        if (!empty($saleChildren)) {
            $saleSection = [
                "SALE",
                SITE_DIR . "catalog/sale/",
                [],
                [
                    "IS_SALE"  => true,
                    "CHILDREN" => $saleChildren
                ],
                ""
            ];

            $menuSections[] = Menu::markIfSelected($saleSection);
        }

        return $menuSections;
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

        $treeSections = [];

        foreach (static::prepareMenuSections($aMenuLinksExt) as $section)
            if ($section[3]['SALE_ONLY_SUBSECTIONS'] && !empty($section[3]['CHILDREN'])) {
                $treeSections = array_merge($treeSections, $section[3]['CHILDREN']);
            } else {
                $treeSections[] = $section;
            }

        return $treeSections;
    }

    /**
     * @param $section
     * @return array
     */
    public static function markIfSelected(array $section): array
    {
        global $APPLICATION;

        $normalizedLink = "/" . rtrim(ltrim($section[1], '/'), '/') . "/";
        $normalizedCurPage = "/" . rtrim(ltrim($APPLICATION->GetCurPage(false), '/'), '/') . "/";

        if (strtolower($normalizedLink) === strtolower($normalizedCurPage)) {
            $section[3]['SELECTED'] = true;
        }

        return $section;
    }

    /**
     * @param array $items
     * @return bool
     */
    public static function setExpanded(array &$items): bool
    {
        $PARAMS = 3;

        foreach ($items as &$item) {
            if ($item[$PARAMS]["SELECTED"]) {
                if (!empty($item[$PARAMS]["CHILDREN"])) {
                    $item[$PARAMS]["EXPANDED"] = true;
                }

                return true;
            } elseif (!empty($item[$PARAMS]["CHILDREN"])) {
                if (static::setExpanded($item[$PARAMS]["CHILDREN"])) {
                    $item[$PARAMS]["EXPANDED"] = true;
                    return true;
                }
            }
        }
        unset($item);

        return false;
    }
}