<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location;

Loc::loadMessages(__FILE__);

if ($arParams["UI_FILTER"])
{
	$arParams["USE_POPUP"] = true;
}

?>

<?if(!empty($arResult['ERRORS']['FATAL'])):?>

	<?foreach($arResult['ERRORS']['FATAL'] as $error):?>
		<?ShowError($error)?>
	<?endforeach?>

<?else:?>

	<?CJSCore::Init();?>
    <?CJSCore::Init(array("fx"));?>
	<?$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/sale/core_ui_widget.js')?>
	<?$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/sale/core_ui_etc.js')?>
	<?$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/sale/core_ui_autocomplete.js');?>
    <?$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/sale/core_ui_pager.js')?>
    <?$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/sale/core_ui_combobox.js')?>
    <?$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/sale/core_ui_chainedselectors.js')?>

	<div id="sls-<?=$arResult['RANDOM_TAG']?>" class="bx-sls <?if(strlen($arResult['MODE_CLASSES'])):?> <?=$arResult['MODE_CLASSES']?><?endif?>">

		<?if(is_array($arResult['DEFAULT_LOCATIONS']) && !empty($arResult['DEFAULT_LOCATIONS'])):?>

			<div class="bx-ui-sls-quick-locations quick-locations">

				<?foreach($arResult['DEFAULT_LOCATIONS'] as $lid => $loc):?>
					<a href="javascript:void(0)" data-id="<?=intval($loc['ID'])?>" class="quick-location-tag"><?=htmlspecialcharsbx($loc['NAME'])?></a>
				<?endforeach?>

			</div>

		<?endif?>

		<? $dropDownBlock = $arParams["UI_FILTER"] ? "dropdown-block-ui" : "dropdown-block"; ?>
		<div class="<?=$dropDownBlock?> bx-ui-sls-input-block form-control js-form__control">

			<span class="dropdown-icon"></span>
			<input type="text"
                   autocomplete="off"
                   name="<?=$arParams['INPUT_NAME']?>"
                   value="<?=$arResult['VALUE']?>"
                   style="display: none"
                   class="dropdown-field js-form__location__value"
                   data-prop="LOCATION"
                   placeholder="<?= $arParams["PLACEHOLDER_TEXT"] ?>"
            />

			<div class="dropdown-fade2white"></div>
			<div class="bx-ui-sls-loader"></div>
			<div class="bx-ui-sls-clear" title="<?=Loc::getMessage('SALE_SLS_CLEAR_SELECTION')?>"></div>
			<div class="bx-ui-sls-pane"></div>

		</div>

		<script type="text/html" data-template-id="bx-ui-sls-error">
			<div class="bx-ui-sls-error">
				<div></div>
				{{message}}
			</div>
		</script>

		<script type="text/html" data-template-id="bx-ui-sls-dropdown-item">
			<div class="dropdown-item bx-ui-sls-variant">
				<span class="dropdown-item-text">{{display_wrapped}}</span>
				<?if($arResult['ADMIN_MODE']):?>
					[{{id}}]
				<?endif?>
			</div>
		</script>

        <div class="invalid-feedback"><?= $arParams['LOCATION_ERROR_MES'] ?></div>

    </div>

	<script>

		if (!window.BX && top.BX)
			window.BX = top.BX;

		<?if(strlen($arParams['JS_CONTROL_DEFERRED_INIT'])):?>
			if(typeof window.BX.locationsDeferred == 'undefined') window.BX.locationsDeferred = {};
			window.BX.locationsDeferred['<?=$arParams['JS_CONTROL_DEFERRED_INIT']?>'] = function(){
		<?endif?>

			<?if(strlen($arParams['JS_CONTROL_GLOBAL_ID'])):?>
				if(typeof window.BX.locationSelectors == 'undefined') window.BX.locationSelectors = {};
				window.BX.locationSelectors['<?=$arParams['JS_CONTROL_GLOBAL_ID']?>'] =
			<?endif?>

			new BX.Sale.component.location.selector.search(<?=CUtil::PhpToJSObject(array(

				// common
				'scope' => 'sls-'.$arResult['RANDOM_TAG'],
				'required' => $arParams['REQUIRED'],
				'source' => "/ajax/findLocations.php",
				'query' => array(
					'FILTER' => array(
						'EXCLUDE_ID' => intval($arParams['EXCLUDE_SUBTREE']),
						'SITE_ID' => $arParams['FILTER_BY_SITE'] && !empty($arParams['FILTER_SITE_ID']) ? $arParams['FILTER_SITE_ID'] : ''
					),
					'BEHAVIOUR' => array(
						'SEARCH_BY_PRIMARY' => $arParams['SEARCH_BY_PRIMARY'] ? '1' : '0',
						'LANGUAGE_ID' => LANGUAGE_ID
					),
				),

				'selectedItem' => !empty($arResult['LOCATION']) ? $arResult['LOCATION']['VALUE'] : false,
				'knownItems' => $arResult['KNOWN_ITEMS'],
				'provideLinkBy' => $arParams['PROVIDE_LINK_BY'],

				'messages' => array(
					'nothingFound' => Loc::getMessage('SALE_SLS_NOTHING_FOUND'),
					'error' => Loc::getMessage('SALE_SLS_ERROR_OCCURED'),
					'sngPlaceholder' => Loc::getMessage('SNG_PLACEHOLDER'),
				),

				// "js logic"-related part
				'callback' => $arParams['JS_CALLBACK'],
				'useSpawn' => $arParams['USE_JS_SPAWN'] == 'Y',
				'usePopup' => ($arParams["USE_POPUP"] ? true : false),
				'initializeByGlobalEvent' => $arParams['INITIALIZE_BY_GLOBAL_EVENT'],
				'globalEventScope' => $arParams['GLOBAL_EVENT_SCOPE'],

				// specific
				'pathNames' => $arResult['PATH_NAMES'], // deprecated
				'types' => $arResult['TYPES'],

			), false, false, true)?>);

		<?if(strlen($arParams['JS_CONTROL_DEFERRED_INIT'])):?>
			};
		<?endif?>

	</script>

    <style>
        .bx-sls .bx-ui-sls-clear {
            background: url(<?=\Level44\Base::getAssetsPath()."/img/clear.svg"?>) center no-repeat scroll;
        }

        .bx-sls .bx-ui-sls-clear:hover {
            background-position: center;
        }

        .bx-sls .dropdown-icon {
            background: url(<?=\Level44\Base::getAssetsPath()."/img/lens.svg"?>) no-repeat center center;
            padding: 8px;
            top: 50%;
            transform: translateY(-50%);
        }
    </style>

<?endif?>
