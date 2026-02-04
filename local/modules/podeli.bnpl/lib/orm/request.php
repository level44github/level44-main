<?php

namespace Podeli\Bnpl\Orm;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\FloatField;
use Bitrix\Main\entity\IntegerField;
use Bitrix\Main\Entity\TextField;

class RequestTable extends Entity\DataManager
{

    public static function getTableName()
    {
        return 'podeli_bnpl_request';
    }

    public static function getMap()
    {
        return [
            'ID' => new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('PODELI.DATA_ENTITY_ID_FIELD'),
            ]),
            'ORDER_ID' => new IntegerField('ORDER_ID', [
                'required' => true,
                'title' => Loc::getMessage('PODELI.DATA_ENTITY_ORDER_ID_FIELD'),
            ]),
            'PAYMENT_ID' => new IntegerField('PAYMENT_ID', [
                'required' => true,
                'title' => Loc::getMessage('PODELI.DATA_ENTITY_PAYMENT_ID_FIELD'),
            ]),
            'ORDER_NUMBER' => new TextField('ORDER_NUMBER', [
                'required' => true,
                'title' => Loc::getMessage('PODELI.DATA_ENTITY_ORDER_NUMBER_FIELD'),
            ]),
            'CORRELATION' => new TextField('CORRELATION', [
                'required' => true,
                'title' => Loc::getMessage('PODELI.DATA_ENTITY_CORRELATION_FIELD'),
            ]),
            'REFUND_ID' => new TextField('REFUND_ID', [
                'default_value' => '',
                'title' => Loc::getMessage('PODELI.DATA_ENTITY_REFUND_ID_FIELD'),
            ]),
            'ITEMS' => new TextField('ITEMS', [
                'required' => true,
                'title' => Loc::getMessage('PODELI.DATA_ENTITY_ITEMS_FIELD'),
            ]),
            'AMOUNT' => new FloatField('AMOUNT', [
                'required' => true,
                'title' => Loc::getMessage('PODELI.DATA_ENTITY_AMOUNT_FIELD'),
            ]),
            'PREPAID_AMOUNT' => new FloatField('PREPAID_AMOUNT', [
                'required' => true,
                'title' => Loc::getMessage('PODELI.DATA_ENTITY_PREPAID_AMOUNT_FIELD'),
            ]),
            'STATUS' => new TextField('STATUS', [
                'required' => true,
                'title' => Loc::getMessage('PODELI.DATA_ENTITY_STATUS_FIELD'),
            ]),
            'PAYMENT_SCHEDULE' => new TextField('PAYMENT_SCHEDULE', [
                'default_value' => null,
                'title' => Loc::getMessage('PODELI.DATA_ENTITY_PAYMENT_SCHEDULE_FIELD'),
            ]),
            'CREATED' => new DatetimeField('CREATED', [
                'default_value' => null,
                'title' => Loc::getMessage('PODELI.DATA_ENTITY_CREATED_FIELD'),
            ])
        ];
    }
}
