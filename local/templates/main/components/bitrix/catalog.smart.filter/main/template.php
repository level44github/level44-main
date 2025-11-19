<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

use Bitrix\Iblock\SectionPropertyTable;

$this->setFrameMode(true);


?>

		<form name="<?echo $arResult["FILTER_NAME"]."_form"?>" action="<?echo $arResult["FORM_ACTION"]?>" method="get" class="catalog__desktop-form">
            <div class="catalog__desktop-filters">
                <div class="catalog__desktop-filters-wrapper">
			<?foreach($arResult["HIDDEN"] as $arItem):?>
			<input type="hidden" name="<?echo $arItem["CONTROL_NAME"]?>" id="<?echo $arItem["CONTROL_ID"]?>" value="<?echo $arItem["HTML_VALUE"]?>" />
			<?endforeach;?>

				<?foreach($arResult["ITEMS"] as $key=>$arItem)//prices
				{
					$key = $arItem["ENCODED_ID"];
					if(isset($arItem["PRICE"])):
						if ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0)
							continue;


						?>

                        <div class="dropdown dropdown--left bx-filter-parameters-box" data-dropdown>
                            <span class="bx-filter-container-modef" style="display: none"></span>
                            <div class="dropdown__header" role="button"><span class="dropdown__title">Цена</span><span
                                        class="dropdown__counter hidden">1</span>
                                <svg class="icon icon-arrow-down dropdown__icon">
                                    <use xlink:href="#arrow-down"></use>
                                </svg>
                            </div>
                            <div class="dropdown__content">
                                <div class="catalog__price-inputs">
                                    <div class="form-group">
                                        <label for="<?echo $arItem["VALUES"]["MIN"]["CONTROL_ID"]?>"></label>
                                        <input class="form-control js-form__control min-price"
                                               type="text"
                                               name="<?echo $arItem["VALUES"]["MIN"]["CONTROL_NAME"]?>"
                                               id="<?echo $arItem["VALUES"]["MIN"]["CONTROL_ID"]?>"
                                               value="<?echo $arItem["VALUES"]["MIN"]["HTML_VALUE"]?>"
                                               onkeyup="smartFilter.keyup(this)"
                                               placeholder="От" />
                                    </div>
                                    <div class="separator"></div>
                                    <div class="form-group">
                                        <label for="<?echo $arItem["VALUES"]["MAX"]["CONTROL_ID"]?>"></label>
                                        <input class="form-control js-form__control max-price" type="text"
                                               name="<?echo $arItem["VALUES"]["MAX"]["CONTROL_NAME"]?>"
                                               id="<?echo $arItem["VALUES"]["MAX"]["CONTROL_ID"]?>"
                                               value="<?echo $arItem["VALUES"]["MAX"]["HTML_VALUE"]?>"
                                               onkeyup="smartFilter.keyup(this)"
                                               placeholder="До" />
                                    </div>
                                </div>
                                <button class="btn btn-dark catalog__price-change-button" type="button" id="set_filter"
                                        name="set_filter">Применить</button>
                            </div>
                        </div>


					<?endif;
				}

				//not prices
				foreach($arResult["ITEMS"] as $key=>$arItem)
				{
					if(
						empty($arItem["VALUES"])
						|| isset($arItem["PRICE"])
					)
						continue;

					if (
						$arItem["DISPLAY_TYPE"] === SectionPropertyTable::NUMBERS_WITH_SLIDER
						&& ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0)
					)
						continue;

					?>



                        <div class="dropdown dropdown--left bx-filter-parameters-box" data-dropdown onclick="smartFilter.hideFilterProps(this)">
                            <div class="dropdown__header" role="button"><span
                                        class="dropdown__title">
                                    <?if ($arItem['CODE']!='COLOR_GROUP_REF'){?>
                                        <?=$arItem["NAME"]?>
                                    <?}else{?>
                                        Цвет
                                    <?}?></span>
                                <span
                                        class="dropdown__counter hidden">1</span>
                                <svg class="icon icon-arrow-down dropdown__icon">
                                    <use xlink:href="#arrow-down"></use>
                                </svg>
                            </div>

						<div class="bx-filter-block" data-role="bx_filter_block">
							<div class="row bx-filter-parameters-box-container">
                                <div class="dropdown__content">
                                    <div class="catalog__filter-group">
                                    <?foreach($arItem["VALUES"] as $val => $ar){
                                        if (!$ar["DISABLED"]){
                                            if ($arItem['CODE']!='COLOR_GROUP_REF')
                                            {
                                            ?>
                                            <label data-role="label_<?=$ar["CONTROL_ID"]?>" class="form-checkbox-desktop js-checkbox-label" for="<? echo $ar["CONTROL_ID"] ?>">
                                                <input type="checkbox" value="<? echo $ar["HTML_VALUE"] ?>"
                                                       name="<? echo $ar["CONTROL_NAME"] ?>"
                                                       id="<? echo $ar["CONTROL_ID"] ?>"
                                                    <? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
                                                       onchange="smartFilter.click(this)"
                                                       >
                                                <span
                                                        class="form-checkbox-desktop__label"><?=$ar["VALUE"];?></span>
                                                <svg class="icon icon-close-small icon">
                                                    <use xlink:href="#close-small"></use>
                                                </svg>
                                            </label>
                                            <?}else{?>
                                                <label class="form-color">
                                                    <input type="checkbox" value="<? echo $ar["HTML_VALUE"] ?>"
                                                           name="<? echo $ar["CONTROL_NAME"] ?>"
                                                           id="<? echo $ar["CONTROL_ID"] ?>"
                                                        <? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
                                                           onchange="smartFilter.click(this)"
                                                    ><span
                                                            class="swatch" style="background:URL('/upload/<?=$ar['FILE']['SUBDIR']?>/<?=$ar['FILE']['FILE_NAME']?>')"></span><span
                                                            class="label-text"><?=$ar["VALUE"];?></span>
                                                    <svg class="icon icon-close-small form-color__icon">
                                                        <use xlink:href="#close-small"></use>
                                                    </svg>
                                                </label>
                                            <?}?>
                                        <?}?>
                                        <?}?>
                                    </div>
                                </div>

							</div>

						</div>
					</div>
				<?
				}
				?>

			<div class="row" style="display:none">
				<div class="col-xs-12 bx-filter-button-box">
					<div class="bx-filter-block">
						<div class="bx-filter-parameters-box-container">
							<input
								class="btn btn-themes"
								type="submit"
								id="set_filter"
								name="set_filter" disabled
								value="<?=GetMessage("CT_BCSF_SET_FILTER")?>"
							/>
							<input
								class="btn btn-link"
								type="submit"
								id="del_filter"
								name="del_filter"
								value="<?=GetMessage("CT_BCSF_DEL_FILTER")?>"
							/>
							<div class="bx-filter-popup-result <?if ($arParams["FILTER_VIEW_MODE"] == "VERTICAL") echo $arParams["POPUP_POSITION"]?>" id="modef" <?if(!isset($arResult["ELEMENT_COUNT"])) echo 'style="display:none"';?> style="display: inline-block;">
								<?echo GetMessage("CT_BCSF_FILTER_COUNT", array("#ELEMENT_COUNT#" => '<span id="modef_num">'.(int)($arResult["ELEMENT_COUNT"] ?? 0).'</span>'));?>
								<span class="arrow"></span>
								<br/>
								<a href="<?echo $arResult["FILTER_URL"]?>" target=""><?echo GetMessage("CT_BCSF_FILTER_SHOW")?></a>
							</div>
						</div>
					</div>
				</div>
			</div>

                </div>
                <div>
                    <div class="dropdown dropdown--right" data-dropdown id="">
                        <div class="dropdown__header" role="button"><span
                                    class="dropdown__title"><?= current(array_filter($arParams["SORT_LIST"], fn($item) => $item["selected"]))['name'] ?></span><span
                                    class="dropdown__counter hidden">1</span>
                            <svg class="icon icon-arrow-down dropdown__icon">
                                <use xlink:href="#arrow-down"></use>
                            </svg>
                        </div>
                        <div class="dropdown__content">
                            <div class="catalog__filter-group">
                                <div class="form-group">
                                    <label class="radio-label" for="form-sort">Сортировать</label>
                                    <div class="form-radio-group catalog__mobile-radio-group">
                                        <? foreach ($arParams["SORT_LIST"] as $sortItem): ?>
                                            <label class="form-radio">
                                                <input type="radio"
                                                       id="form-sort-<?= $sortItem["code"] ?>"
                                                       name="sort"
                                                       data-sort-cookie-name="<?= $arParams['SORT_COOKIE_NAME'] ?>"
                                                       value="<?= $sortItem["code"] ?>"
                                                    <?= $sortItem["selected"] ? "checked" : '' ?>
                                                >
                                                <span><?= $sortItem["name"] ?></span>
                                            </label>
                                        <? endforeach; ?>
                                    </div>
                                    <div class="invalid-feedback">Пожалуйста, выберите значение</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		</form>





<div class="bottom-sheet" id="filters-sheet">
    <div class="bottom-sheet__overlay"></div>
    <div class="bottom-sheet__container">
        <div class="bottom-sheet__header">
            <h2>Фильтры</h2>
            <button class="btn btn-text bottom-sheet__close" type="button" aria-label="Close">
                <svg class="icon icon-close bottom-sheet__close-icon">
                    <use xlink:href="#close"></use>
                </svg>
            </button>
        </div>
        <div class="bottom-sheet__content">
            <form id="filters-form" class="js-mobile-filters" action="<?= $APPLICATION->GetCurPageParam('', ['sort']) ?>" method="GET">
                <?foreach($arResult["HIDDEN"] as $arItem):?>
                    <input type="hidden" name="<?echo $arItem["CONTROL_NAME"]?>" id="<?echo $arItem["CONTROL_ID"]?>" value="<?echo $arItem["HTML_VALUE"]?>" />
                <?endforeach;?>
                <div class="form-group">
                    <label class="radio-label" for="form-sort">Сортировать</label>
                    <div class="form-radio-group catalog__mobile-radio-group">
                        <? foreach ($arParams["SORT_LIST"] as $sortItem): ?>
                            <label class="form-radio">
                                <input type="radio"
                                       id="form-sort-<?= $sortItem["code"] ?>"
                                       name="sort"
                                       data-sort-cookie-name="<?= $arParams['SORT_COOKIE_NAME'] ?>"
                                       data-section="<?=$arParams['SECTION_ID']?>"
                                       value="<?= $sortItem["code"] ?>"
                                    <?= $sortItem["selected"] ? "checked" : '' ?>
                                >
                                <span><?= $sortItem["name"] ?></span>
                            </label>
                        <? endforeach; ?>
                    </div>
                    <div class="invalid-feedback">Пожалуйста, выберите значение</div>
                </div>
            </form>
                <div class="catalog__mobile-filters">
                    <form name="<?echo $arResult["FILTER_NAME"]."_form"?>" action="<?echo $arResult["FORM_ACTION"]?>">

                        <?foreach($arResult["ITEMS"] as $key=>$arItem)
                        {
                        if(
                        empty($arItem["VALUES"])
                        || isset($arItem["PRICE"])
                        )
                        continue;

                        if (
                        $arItem["DISPLAY_TYPE"] === SectionPropertyTable::NUMBERS_WITH_SLIDER
                        && ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0)
                        )
                        continue;
                        ?>
                        <div class="accordion">
                            <button class="btn btn-link accordion__trigger" type="button"
                                    aria-label="Toggle accordion">
                                <div class="accordion__title">
                                    <?if ($arItem['CODE']!='COLOR_GROUP_REF'){?>
                                    <?=$arItem["NAME"]?>
                                    <?}else{?>
                                        Цвет
                                    <?}?>
                                </div>
                                <svg class="icon icon-arrow-down accordion__icon">
                                    <use xlink:href="#arrow-down"></use>
                                </svg>
                            </button>
                            <div class="accordion__content">
                                <div class="catalog__mobile-input-group">
                                    <?foreach($arItem["VALUES"] as $val => $ar){
                                    if (!$ar["DISABLED"]){
                                        if ($arItem['CODE']!='COLOR_GROUP_REF')
                                        {
                                        ?>

                                        <label class="form-checkbox">
                                            <input type="checkbox"   name="<? echo $ar["CONTROL_NAME"] ?>"
                                                   id="<? echo $ar["CONTROL_ID"] ?>"
                                                   value="<? echo $ar["HTML_VALUE"] ?>"
                                                <? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
                                                   onclick="smartFilter.click(this)"><span><?=$ar["VALUE"];?></span>
                                        </label>
                                        <?}else{?>
                                        <label class="form-color">
                                            <input type="checkbox" value="<? echo $ar["HTML_VALUE"] ?>"
                                                   name="<? echo $ar["CONTROL_NAME"] ?>"
                                                   id="<? echo $ar["CONTROL_ID"] ?>"
                                                <? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
                                                   onchange="smartFilter.click(this)"
                                            ><span
                                                    class="swatch" style="background:URL('/upload/<?=$ar['FILE']['SUBDIR']?>/<?=$ar['FILE']['FILE_NAME']?>')"></span><span
                                                    class="label-text"><?=$ar["VALUE"];?></span>
                                            <svg class="icon icon-close-small form-color__icon">
                                                <use xlink:href="#close-small"></use>
                                            </svg>
                                        </label>
                                            <?}?>
                                    <?}?>
                                    <?}?>

                                </div>
                            </div>
                        </div>
                        <?}?>


                        <?foreach($arResult["ITEMS"] as $key=>$arItem)//prices
                        {
                        $key = $arItem["ENCODED_ID"];
                        if(isset($arItem["PRICE"])):
                        if ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0)
                            continue;


                        ?>

                            <div class="accordion">
                                <span class="bx-filter-container-modef" style="display: none"></span>
                                <button class="btn btn-link accordion__trigger" type="button"
                                        aria-label="Toggle accordion">
                                    <div class="accordion__title">Цена</div>
                                    <svg class="icon icon-arrow-down accordion__icon">
                                        <use xlink:href="#arrow-down"></use>
                                    </svg>
                                </button>
                                <div class="accordion__content">
                                    <div class="catalog__mobile-price-inputs">
                                        <div class="form-group">
                                            <label for="<?echo $arItem["VALUES"]["MIN"]["CONTROL_ID"]?>"></label>
                                            <input class="form-control js-form__control min-price"
                                                   type="text"
                                                   name="<?echo $arItem["VALUES"]["MIN"]["CONTROL_NAME"]?>"
                                                   id="<?echo $arItem["VALUES"]["MIN"]["CONTROL_ID"]?>"
                                                   value="<?echo $arItem["VALUES"]["MIN"]["HTML_VALUE"]?>"
                                                   onkeyup="smartFilter.keyup(this)"
                                                   placeholder="От" />
                                        </div>
                                        <div class="separator"></div>
                                        <div class="form-group">
                                            <label for="<?echo $arItem["VALUES"]["MAX"]["CONTROL_ID"]?>"></label>
                                            <input class="form-control js-form__control max-price" type="text"
                                                   name="<?echo $arItem["VALUES"]["MAX"]["CONTROL_NAME"]?>"
                                                   id="<?echo $arItem["VALUES"]["MAX"]["CONTROL_ID"]?>"
                                                   value="<?echo $arItem["VALUES"]["MAX"]["HTML_VALUE"]?>"
                                                   onkeyup="smartFilter.keyup(this)"
                                                   placeholder="До" />
                                        </div>
                                    </div>
                                </div>
                            </div>


                        <?endif;
				}?>





                    <div></div>
                </div>

                <div class="catalog__mobile-buttons">
                    <button class="btn btn-light" type="submit"
                            id="del_filter"
                            name="del_filter"
                            value="<?=GetMessage("CT_BCSF_DEL_FILTER")?>">Сбросить</button>
                    <button class="btn btn-dark" type="submit" disabled
                            id="set_filter2"
                            name="set_filter"
                            value="<?=GetMessage("CT_BCSF_SET_FILTER")?>">Показать</button>
                </div>


            </form>
        </div>
    </div>
</div>

<script>
	var smartFilter = new JCSmartFilter('<?echo CUtil::JSEscape($arResult["FORM_ACTION"])?>', '<?=CUtil::JSEscape($arParams["FILTER_VIEW_MODE"])?>', <?=CUtil::PhpToJSObject($arResult["JS_FILTER_PARAMS"])?>);


</script>