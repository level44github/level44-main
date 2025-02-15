<?php

namespace Level44;

use Bitrix\Main\Context;

class Exchange1C
{
    private static function isSource1C()
    {
        $request = Context::getCurrent()->getRequest();
        return $request->get('type') === 'catalog'
            && $request->get('mode') === 'import'
            && $request->get('type') === 'catalog'
            && str_contains($GLOBALS['APPLICATION']->GetCurPage(), '1c_exchange.php');
    }

    static function handleAddProduct(array &$arFields): void
    {
        if (static::isSource1C() && (int)$arFields["IBLOCK_ID"] === Base::CATALOG_IBLOCK_ID) {
            $arFields["ACTIVE"] = 'N';
        }
    }

    static function handleAddSection(array &$arFields): void
    {
        if (static::isSource1C() && (int)$arFields["IBLOCK_ID"] === Base::CATALOG_IBLOCK_ID) {
            $arFields["ACTIVE"] = 'N';
        }
    }

    static function handleUpdateProduct(array &$arFields): void
    {
        if (static::isSource1C() && (int)$arFields["IBLOCK_ID"] === Base::CATALOG_IBLOCK_ID) {
            $res = \CIBlockElement::GetElementGroups($arFields["ID"], true, ['ID']);

            $sections = [];

            while ($section = $res->GetNext()) {
                $sections[] = $section['ID'];
            }

            if (!empty($sections)) {
                $arFields['IBLOCK_SECTION'] = $sections;
            }
        }
    }
}