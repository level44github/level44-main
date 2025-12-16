<?

include_once(dirname(__FILE__) . "/include.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;

$module_id = "podeli.bnpl";
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php");
Loc::loadMessages(__FILE__);

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

$arAllOptions = [
    [
        "debug",
        Loc::getMessage("PODELI.DEBUG_KEY"),
        ["radio", false],
        "",
    ],
    [
        "write_log",
        Loc::getMessage("PODELI.WRITE_LOG"),
        ["radio", false],
        Loc::getMessage("PODELI.WRITE_LOG_DESCRIPTION"),
    ],
    [
        "uninstall_with_db",
        Loc::getMessage("PODELI.UNINSTALL_WITH_DB"),
        ["radio", false],
        '',
    ],
    [
        "auto_commit",
        Loc::getMessage("PODELI.AUTO_COMMIT_WHEN_PROCESS_PAY"),
        ['radio', false],
        '',
    ],
    [
        'article_key',
        Loc::getMessage('PODELI.ARTICLE_KEY'),
        ['text', 255],
        '',
    ],
    [
        'show_widget',
        Loc::getMessage('PODELI.SHOW_WIDGET_KEY'),
        ['radio', false],
        '',
    ],

    [
        'cart_widget_theme',
        Loc::getMessage('PODELI.CART_WIDGET_THEME'),
        [ 
            'list', [
                'light' => Loc::getMessage('PODELI.CART_WIDGET_THEME_LIGHT'),
                'dark' => Loc::getMessage('PODELI.CART_WIDGET_THEME_DARK'),
            ]
        ],
        '',
    ],
    [
        'short_badge_widget_type',
        Loc::getMessage('PODELI.SHORT_BADGE_WIDGET_TYPE'),
        [
            'list', [
                'mini' => Loc::getMessage('PODELI.SHORT_BADGE_WIDGET_TYPE_MINI'),
                'text' => Loc::getMessage('PODELI.SHORT_BADGE_WIDGET_TYPE_TEXT'),
                'v2' => Loc::getMessage('PODELI.SHORT_BADGE_WIDGET_TYPE_V2')
            ]
        ],
        '',
    ],
    [
        'short_badge_widget_mode',
        Loc::getMessage('PODELI.SHORT_BADGE_WIDGET_MODE'),
        [
            'list', [
                'none' => Loc::getMessage('PODELI.SHORT_BADGE_WIDGET_MODE_NONE'),
                'red' => Loc::getMessage('PODELI.SHORT_BADGE_WIDGET_MODE_RED'),
                'silver' => Loc::getMessage('PODELI.SHORT_BADGE_WIDGET_MODE_SILVER'),
                'shadow' => Loc::getMessage('PODELI.SHORT_BADGE_WIDGET_MODE_SHADOW')
            ]
        ],
        '',
    ],
    [
        'show_header_widget',
        Loc::getMessage('PODELI.SHOW_HEADER_WIDGET'),
        ['radio', true],
        '',
    ],
    [
        'header_widget_animate',
        Loc::getMessage('PODELI.HEADER_WIDGET_ANIMATE'),
        ['radio', true],
        '',
    ],
    [
        'header_widget_mode',
        Loc::getMessage('PODELI.HEADER_WIDGET_MODE'),
        [
            'list', [
                'none' => Loc::getMessage('PODELI.HEADER_WIDGET_MODE_NONE'),
                'red' => Loc::getMessage('PODELI.HEADER_WIDGET_MODE_RED'),
                'shadow' => Loc::getMessage('PODELI.HEADER_WIDGET_MODE_SHADOW')
            ]
        ],
        '',
    ],

    [
        'remove_refunded_items_from_order',
        Loc::getMessage('PODELI.REMOVE_REFUNDED_ITEMS_FROM_ORDER'),
        ['radio', true],
        '',
    ],
    [
        'discount',
        Loc::getMessage('PODELI.ADD_DISCOUNT'),
        ['radio', false],
        '',
    ],
    [
        'widget_payment_min_limit',
        Loc::getMessage('PODELI.WIDGET_PAYMENT_MIN_LIMIT'),
        ['text', 255],
        '',
    ],
    [
        'widget_payment_max_limit',
        Loc::getMessage('PODELI.WIDGET_PAYMENT_MAX_LIMIT'),
        ['text', 255],
        '',
    ],
    [
        'multi_order_request',
        Loc::getMessage('PODELI.MULTI_ORDER'),
        ['radio', false],
        '',
    ],
    [
        'use_curl_handler',
        Loc::getMessage('PODELI.USE_CURL_HANDLER'),
        ['radio', true],
        '',
    ],
    [
        'redirect_exclude_list',
        Loc::getMessage('PODELI.PAYMENT_EXCLUDE_LIST'),
        ['textarea', ''],
        Loc::getMessage('PODELI.PAYMENT_EXCLUDE_LIST_DESC'),        
    ],
];

\Bitrix\Main\Loader::includeModule('sale');

$arStatuses = \Bitrix\Sale\OrderStatus::getAllStatusesNames();
array_unshift($arStatuses, Loc::getMessage('PODELI.BINDING_STATUS_NOT_SET'));

$arAllOptions[] = [
    'payed_status_binding',
    Loc::getMessage('PODELI.PAYED_STATUS_BINDING'),
    ['list', $arStatuses],
    Loc::getMessage('PODELI.PAYED_STATUS_BINDING_DESC'),
];

$arAllOptions[] = [
    'cancelled_status_binding',
    Loc::getMessage('PODELI.CANCELLED_STATUS_BINDING'),
    ['list', $arStatuses],
    Loc::getMessage('PODELI.CANCELLED_STATUS_BINDING_DESC'),
];

$aTabs = [
    [
        'DIV' => 'edit1',
        'TAB' => Loc::getMessage('MAIN_TAB_SET'),
        'ICON' => null, //$module_id . '_settings'
        'TITLE' => Loc::getMessage('MAIN_TAB_TITLE_SET')
    ]
];

$tabControl = new CAdminTabControl('tabControl', $aTabs);

if ($_REQUEST['Update'] == 'Y' && $request->isPost() && check_bitrix_sessid()) {
    foreach ($arAllOptions as $arAllOption) {
        $name = $arAllOption[0];
        $val = $request->getPost($name);
        Option::set($module_id, $name, $val);
    }
}

?>
<form method="post" action="<?php echo $APPLICATION->GetCurPage(); ?>?mid=<?php echo urlencode($mid); ?>&amp;lang=<?php echo LANGUAGE_ID; ?>" enctype="multipart/form-data"><?php
                                                                                                                                                                            $tabControl->Begin();
                                                                                                                                                                            $tabControl->BeginNextTab(); ?>
    <?php require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php"); ?>
    <?php foreach ($arAllOptions as $arOption) :
        $val = Option::get($module_id, $arOption[0]);
        $type = $arOption[2];
    ?><tr>
            <td valign="top" width="50%"><?php
                                            echo $arOption[1]; ?><br><small><?php echo $arOption[3]; ?></small></td>
            <td valign="top" width="50%">
                <?php if ($type[0] == "text") : ?>
                    <input type="text" size="<?php echo $type[1]; ?>" maxlength="255" value="<?php echo htmlspecialchars($val); ?>" name="<?php echo htmlspecialchars($arOption[0]); ?>">
                <?php elseif ($type[0] == "textarea") : ?>
                    <textarea style="width:100%;height:300px;" name="<?php echo htmlspecialchars($arOption[0]); ?>"><?php echo htmlspecialchars($val); ?></textarea>
                <?php elseif ($type[0] == "radio") : ?>
                    <input type="radio" value="1" name="<?php echo htmlspecialchars($arOption[0]); ?>" <?php if ($val == 1) echo 'checked="checked"'; ?> /> <?php echo Loc::getMessage('PODELI.PAYMENT_OPTIONS_YES') ?> <br />
                    <input type="radio" value="0" name="<?php echo htmlspecialchars($arOption[0]); ?>" <?php if ($val == 0) echo 'checked="checked"'; ?> /> <?php echo Loc::getMessage('PODELI.PAYMENT_OPTIONS_NO') ?>
                <?php elseif ($type[0] == "list") : ?>
                    <select name="<?php echo htmlspecialchars($arOption[0]); ?>">
                        <?php foreach ($type[1] as $key => $value) : ?>
                            <option value="<?php echo $key; ?>" <?php if ($val == $key) echo 'selected="selected"'; ?>><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>

    <?php
    echo '<input type="hidden" name="Update" value="Y" />';
    $tabControl->Buttons(["back_url" => $APPLICATION->GetCurPage() . '?lang=' . LANGUAGE_ID]);
    $tabControl->End();
    ?>

    <?php echo bitrix_sessid_post(); ?>
</form>
