<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Level44\Base;
use Bitrix\Highloadblock\HighloadBlockTable;

$clothesSizeRef = [];
$shoesSizeRef = [];

try {
    $hlblock = HighloadBlockTable::getList([
        'filter' => [
            '=TABLE_NAME' => Base::SIZE_HL_TBL_NAME
        ]
    ])->fetch();

    if ($hlblock) {
        $entity = HighloadBlockTable::compileEntity($hlblock);
        $entityClass = $entity->getDataClass();

        $res = $entityClass::getList(
            [
                "select" => [
                    "ID",
                    "UF_NAME",
                    "UF_XML_ID",
                    "UF_TYPE"
                ],
                "order"  => ['UF_SORT' => 'ASC']
            ]
        );

        $rs = CUserTypeEntity::GetList(
            [],
            ['FIELD_NAME' => 'UF_TYPE', 'ENTITY_ID' => "HLBLOCK_{$hlblock['ID']}"]
        );

        $userField = $rs->GetNext();

        if (!empty($userField)) {
            $rsUserFieldEnums = CUserFieldEnum::GetList([], ['USER_FIELD_ID' => $userField['ID']]);

            $userFieldEnums = [];
            while ($userFieldEnum = $rsUserFieldEnums->GetNext()) {
                $userFieldEnums[$userFieldEnum['XML_ID']] = $userFieldEnum['ID'];
            }

            while ($size = $res->fetch()) {
                switch ($size['UF_TYPE']) {
                    case $userFieldEnums['clothes']:
                        $clothesSizeRef[] = $size;
                        break;
                    case $userFieldEnums['shoes']:
                        $shoesSizeRef[] = $size;
                        break;
                }
            }
        }
    }
} catch (\Exception $exception) {
}

$arResult["CLOTHES_SIZE_REF"] = $clothesSizeRef;
$arResult["SHOES_SIZE_REF"] = $shoesSizeRef;