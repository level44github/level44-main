<?php

namespace Level44\OsmiCard;

use Bitrix\Main\UserFieldTable;

/**
 * Класс для установки необходимых полей и настроек OSMI Card
 */
class Installer
{
    /**
     * Установка пользовательского поля для номера карты
     * 
     * @return bool
     */
    public static function installUserField(): bool
    {
        global $USER_FIELD_MANAGER;

        // Проверяем, существует ли уже поле
        $existingField = UserFieldTable::getList([
            'filter' => [
                'ENTITY_ID' => 'USER',
                'FIELD_NAME' => 'UF_OSMI_CARD_NUMBER'
            ]
        ])->fetch();

        if ($existingField) {
            return true; // Поле уже существует
        }

        $arFields = [
            'ENTITY_ID' => 'USER',
            'FIELD_NAME' => 'UF_OSMI_CARD_NUMBER',
            'USER_TYPE_ID' => 'string',
            'SORT' => 500,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'Y',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'Y',
            'SETTINGS' => [
                'DEFAULT_VALUE' => '',
                'SIZE' => 20,
                'ROWS' => 1,
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'REGEXP' => '',
            ],
            'EDIT_FORM_LABEL' => [
                'ru' => 'Номер карты OSMI',
                'en' => 'OSMI Card Number',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Номер карты OSMI',
                'en' => 'OSMI Card Number',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Номер карты OSMI',
                'en' => 'OSMI Card Number',
            ],
            'ERROR_MESSAGE' => [
                'ru' => '',
                'en' => '',
            ],
            'HELP_MESSAGE' => [
                'ru' => 'Номер карты лояльности OSMI Card',
                'en' => 'OSMI Card loyalty card number',
            ],
        ];

        $userField = new \CUserTypeEntity();
        $fieldId = $userField->Add($arFields);

        return (bool)$fieldId;
    }

    /**
     * Установка пользовательского поля для хранения ID карты в OSMI
     * 
     * @return bool
     */
    public static function installUserCardIdField(): bool
    {
        global $USER_FIELD_MANAGER;

        // Проверяем, существует ли уже поле
        $existingField = UserFieldTable::getList([
            'filter' => [
                'ENTITY_ID' => 'USER',
                'FIELD_NAME' => 'UF_OSMI_CARD_ID'
            ]
        ])->fetch();

        if ($existingField) {
            return true; // Поле уже существует
        }

        $arFields = [
            'ENTITY_ID' => 'USER',
            'FIELD_NAME' => 'UF_OSMI_CARD_ID',
            'USER_TYPE_ID' => 'string',
            'SORT' => 501,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'N',
            'EDIT_IN_LIST' => 'N',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'DEFAULT_VALUE' => '',
                'SIZE' => 20,
                'ROWS' => 1,
            ],
            'EDIT_FORM_LABEL' => [
                'ru' => 'ID карты в OSMI',
                'en' => 'OSMI Card ID',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'ID карты в OSMI',
                'en' => 'OSMI Card ID',
            ],
        ];

        $userField = new \CUserTypeEntity();
        $fieldId = $userField->Add($arFields);

        return (bool)$fieldId;
    }

    /**
     * Полная установка всех необходимых полей
     * 
     * @return array
     */
    public static function install(): array
    {
        $results = [];

        $results['userField'] = self::installUserField();
        $results['cardIdField'] = self::installUserCardIdField();

        return $results;
    }

    /**
     * Удаление пользовательских полей
     * 
     * @return bool
     */
    public static function uninstall(): bool
    {
        $fields = ['UF_OSMI_CARD_NUMBER', 'UF_OSMI_CARD_ID'];

        foreach ($fields as $fieldName) {
            $field = UserFieldTable::getList([
                'filter' => [
                    'ENTITY_ID' => 'USER',
                    'FIELD_NAME' => $fieldName
                ]
            ])->fetch();

            if ($field) {
                $userField = new \CUserTypeEntity();
                $userField->Delete($field['ID']);
            }
        }

        return true;
    }
}

