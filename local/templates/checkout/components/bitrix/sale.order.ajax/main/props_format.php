<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<?
if (!function_exists("showFilePropertyField")) {
    function showFilePropertyField($name, $property_fields, $values, $max_file_size_show = 50000)
    {
        $res = "";

        if (!is_array($values) || empty($values)) {
            $values = array(
                "n0" => 0,
            );
        }

        if ($property_fields["MULTIPLE"] == "N") {
            $res = "<label for=\"\"><input type=\"file\" size=\"" . $max_file_size_show . "\" value=\"" . $property_fields["VALUE"] . "\" name=\"" . $name . "[0]\" id=\"" . $name . "[0]\"></label>";
        } else {
            $res = '
			<script type="text/javascript">
				function addControl(item)
				{
					var current_name = item.id.split("[")[0],
						current_id = item.id.split("[")[1].replace("[", "").replace("]", ""),
						next_id = parseInt(current_id) + 1;

					var newInput = document.createElement("input");
					newInput.type = "file";
					newInput.name = current_name + "[" + next_id + "]";
					newInput.id = current_name + "[" + next_id + "]";
					newInput.onchange = function() { addControl(this); };

					var br = document.createElement("br");
					var br2 = document.createElement("br");

					BX(item.id).parentNode.appendChild(br);
					BX(item.id).parentNode.appendChild(br2);
					BX(item.id).parentNode.appendChild(newInput);
				}
			</script>
			';

            $res .= "<label for=\"\"><input type=\"file\" size=\"" . $max_file_size_show . "\" value=\"" . $property_fields["VALUE"] . "\" name=\"" . $name . "[0]\" id=\"" . $name . "[0]\"></label>";
            $res .= "<br/><br/>";
            $res .= "<label for=\"\"><input type=\"file\" size=\"" . $max_file_size_show . "\" value=\"" . $property_fields["VALUE"] . "\" name=\"" . $name . "[1]\" id=\"" . $name . "[1]\" onChange=\"javascript:addControl(this);\"></label>";
        }

        return $res;
    }
}

if (!function_exists("PrintPropsForm")) {
    function PrintPropsForm($arProperty = array(), $locationTemplate = ".default")
    {
        if (CSaleLocation::isLocationProMigrated()) {
            $propertyAttributes = array(
                'type' => $arProperty["TYPE"],
                'valueSource' => $arProperty['SOURCE'] == 'DEFAULT' ? 'default' : 'form'
            );

            if (intval($arProperty['IS_ALTERNATE_LOCATION_FOR'])) {
                $propertyAttributes['isAltLocationFor'] = intval($arProperty['IS_ALTERNATE_LOCATION_FOR']);
            }

            if (intval($arProperty['INPUT_FIELD_LOCATION'])) {
                $propertyAttributes['altLocationPropId'] = intval($arProperty['INPUT_FIELD_LOCATION']);
            }

            if ($arProperty['IS_ZIP'] == 'Y') {
                $propertyAttributes['isZip'] = true;
            }
        }
        ?>
        <?
        if ($arProperty["TYPE"] == "CHECKBOX" && false):?>
            <input type="hidden" name="<?= $arProperty["FIELD_NAME"] ?>" value="">

            <div class="bx_block r1x3 pt8">
                <?= $arProperty["NAME"] ?>
                <?
                if ($arProperty["REQUIED_FORMATED"] == "Y"):?>
                    <span class="bx_sof_req">*</span>
                <? endif; ?>
            </div>

            <div class="bx_block r1x3 pt8">
                <input type="checkbox" name="<?= $arProperty["FIELD_NAME"] ?>" id="<?= $arProperty["FIELD_NAME"] ?>"
                       value="Y"<?
                if ($arProperty["CHECKED"] == "Y") {
                    echo " checked";
                } ?>>

                <?
                if (strlen(trim($arProperty["DESCRIPTION"])) > 0):
                    ?>
                    <div class="bx_description">
                        <?= $arProperty["DESCRIPTION"] ?>
                    </div>
                <?
                endif;
                ?>
            </div>

            <div style="clear: both;"></div>

        <? elseif ($arProperty["TYPE"] == "TEXT"): ?>
            <div class="form-group">
                <label for="form-email"><?= $arProperty["NAME"] ?></label>
                <input type="text"
                       maxlength="250"
                       size="<?= $arProperty["SIZE1"] ?>"
                       class="form-control js-form__control js-form__email"
                       id="form-email <?= $arProperty["FIELD_NAME"] ?>"
                       name="<?= $arProperty["FIELD_NAME"] ?>"
                       value="<?= $arProperty["VALUE"] ?>"
                       placeholder="Введите эл. почту">
                <div class="invalid-feedback">Недопустимые символы в поле</div>
            </div>
        <? elseif ($arProperty["TYPE"] == "SELECT" && false): ?>
            <br/>
            <div class="bx_block r1x3 pt8">
                <?= $arProperty["NAME"] ?>
                <?
                if ($arProperty["REQUIED_FORMATED"] == "Y"):?>
                    <span class="bx_sof_req">*</span>
                <? endif; ?>
            </div>

            <div class="bx_block r3x1">
                <select name="<?= $arProperty["FIELD_NAME"] ?>" id="<?= $arProperty["FIELD_NAME"] ?>"
                        size="<?= $arProperty["SIZE1"] ?>">
                    <?
                    foreach ($arProperty["VARIANTS"] as $arVariants):
                        ?>
                        <option value="<?= $arVariants["VALUE"] ?>"<?
                        if ($arVariants["SELECTED"] == "Y") {
                            echo " selected";
                        } ?>><?= $arVariants["NAME"] ?></option>
                    <?
                    endforeach;
                    ?>
                </select>

                <?
                if (strlen(trim($arProperty["DESCRIPTION"])) > 0):
                    ?>
                    <div class="bx_description">
                        <?= $arProperty["DESCRIPTION"] ?>
                    </div>
                <?
                endif;
                ?>
            </div>
            <div style="clear: both;"></div>
        <? elseif ($arProperty["TYPE"] == "MULTISELECT" && false): ?>
            <br/>
            <div class="bx_block r1x3 pt8">
                <?= $arProperty["NAME"] ?>
                <?
                if ($arProperty["REQUIED_FORMATED"] == "Y"):?>
                    <span class="bx_sof_req">*</span>
                <? endif; ?>
            </div>

            <div class="bx_block r3x1">
                <select multiple name="<?= $arProperty["FIELD_NAME"] ?>" id="<?= $arProperty["FIELD_NAME"] ?>"
                        size="<?= $arProperty["SIZE1"] ?>">
                    <?
                    foreach ($arProperty["VARIANTS"] as $arVariants):
                        ?>
                        <option value="<?= $arVariants["VALUE"] ?>"<?
                        if ($arVariants["SELECTED"] == "Y") {
                            echo " selected";
                        } ?>><?= $arVariants["NAME"] ?></option>
                    <?
                    endforeach;
                    ?>
                </select>

                <?
                if (strlen(trim($arProperty["DESCRIPTION"])) > 0):
                    ?>
                    <div class="bx_description">
                        <?= $arProperty["DESCRIPTION"] ?>
                    </div>
                <?
                endif;
                ?>
            </div>
            <div style="clear: both;"></div>
        <?
        elseif ($arProperty["TYPE"] == "TEXTAREA" && false):
            $rows = ($arProperty["SIZE2"] > 10) ? 4 : $arProperty["SIZE2"];
            ?>
            <br/>
            <div class="bx_block r1x3 pt8">
                <?= $arProperty["NAME"] ?>
                <?
                if ($arProperty["REQUIED_FORMATED"] == "Y"):?>
                    <span class="bx_sof_req">*</span>
                <? endif; ?>
            </div>

            <div class="bx_block r3x1">
                <textarea rows="<?= $rows ?>" cols="<?= $arProperty["SIZE1"] ?>" name="<?= $arProperty["FIELD_NAME"] ?>"
                          id="<?= $arProperty["FIELD_NAME"] ?>"><?= $arProperty["VALUE"] ?></textarea>

                <?
                if (strlen(trim($arProperty["DESCRIPTION"])) > 0):
                    ?>
                    <div class="bx_description">
                        <?= $arProperty["DESCRIPTION"] ?>
                    </div>
                <?
                endif;
                ?>
            </div>
            <div style="clear: both;"></div>
        <? elseif ($arProperty["TYPE"] == "LOCATION"): ?>
            <div class="form-group bx_block">
                <label for="form-email"><?= $arProperty["NAME"] ?></label>
                <?
                $value = 0;
                if (is_array($arProperty["VARIANTS"]) && count($arProperty["VARIANTS"]) > 0) {
                    foreach ($arProperty["VARIANTS"] as $arVariant) {
                        if ($arVariant["SELECTED"] == "Y") {
                            $value = $arVariant["ID"];
                            break;
                        }
                    }
                }
                ?>

                <?
                CSaleLocation::proxySaleAjaxLocationsComponent(array(
                    "AJAX_CALL" => "N",
                    "COUNTRY_INPUT_NAME" => "COUNTRY",
                    "REGION_INPUT_NAME" => "REGION",
                    "CITY_INPUT_NAME" => $arProperty["FIELD_NAME"],
                    "CITY_OUT_LOCATION" => "Y",
                    "LOCATION_VALUE" => $value,
                    "ORDER_PROPS_ID" => $arProperty["ID"],
                    "ONCITYCHANGE" => ($arProperty["IS_LOCATION"] == "Y" || $arProperty["IS_LOCATION4TAX"] == "Y") ? "submitChangeLocation()" : "",
                    "SIZE1" => $arProperty["SIZE1"],
                ),
                    array(
                        "ID" => $arProperty["VALUE"],
                        "CODE" => "",
                        "SHOW_DEFAULT_LOCATIONS" => "Y",

                        // function called on each location change caused by user or by program
                        // it may be replaced with global component dispatch mechanism coming soon
                        "JS_CALLBACK" => "submitFormProxy",
                        //($arProperty["IS_LOCATION"] == "Y" || $arProperty["IS_LOCATION4TAX"] == "Y") ? "submitFormProxy" : "",

                        // function window.BX.locationsDeferred['X'] will be created and lately called on each form re-draw.
                        // it may be removed when sale.order.ajax will use real ajax form posting with BX.ProcessHTML() and other stuff instead of just simple iframe transfer
                        "JS_CONTROL_DEFERRED_INIT" => intval($arProperty["ID"]),

                        // an instance of this control will be placed to window.BX.locationSelectors['X'] and lately will be available from everywhere
                        // it may be replaced with global component dispatch mechanism coming soon
                        "JS_CONTROL_GLOBAL_ID" => intval($arProperty["ID"]),

                        "DISABLE_KEYBOARD_INPUT" => 'Y'
                    ),
                    ".default",
                    true,
                    'location-block-wrapper'
                ) ?>

            </div>
        <? elseif ($arProperty["TYPE"] == "RADIO" && false): ?>
            <div class="bx_block r1x3 pt8">
                <?= $arProperty["NAME"] ?>
                <?
                if ($arProperty["REQUIED_FORMATED"] == "Y"):?>
                    <span class="bx_sof_req">*</span>
                <? endif; ?>
            </div>

            <div class="bx_block r3x1">
                <?
                if (is_array($arProperty["VARIANTS"])) {
                    foreach ($arProperty["VARIANTS"] as $arVariants):
                        ?>
                        <input
                                type="radio"
                                name="<?= $arProperty["FIELD_NAME"] ?>"
                                id="<?= $arProperty["FIELD_NAME"] ?>_<?= $arVariants["VALUE"] ?>"
                                value="<?= $arVariants["VALUE"] ?>" <?
                        if ($arVariants["CHECKED"] == "Y") {
                            echo " checked";
                        } ?> />

                        <label for="<?= $arProperty["FIELD_NAME"] ?>_<?= $arVariants["VALUE"] ?>"><?= $arVariants["NAME"] ?></label></br>
                    <?
                    endforeach;
                }
                ?>

                <?
                if (strlen(trim($arProperty["DESCRIPTION"])) > 0):
                    ?>
                    <div class="bx_description">
                        <?= $arProperty["DESCRIPTION"] ?>
                    </div>
                <?
                endif;
                ?>
            </div>
            <div style="clear: both;"></div>
        <? elseif ($arProperty["TYPE"] == "FILE" && false): ?>
            <br/>
            <div class="bx_block r1x3 pt8">
                <?= $arProperty["NAME"] ?>
                <?
                if ($arProperty["REQUIED_FORMATED"] == "Y"):?>
                    <span class="bx_sof_req">*</span>
                <? endif; ?>
            </div>

            <div class="bx_block r3x1">
                <?= showFilePropertyField("ORDER_PROP_" . $arProperty["ID"], $arProperty, $arProperty["VALUE"],
                    $arProperty["SIZE1"]) ?>

                <?
                if (strlen(trim($arProperty["DESCRIPTION"])) > 0):
                    ?>
                    <div class="bx_description">
                        <?= $arProperty["DESCRIPTION"] ?>
                    </div>
                <?
                endif;
                ?>
            </div>

            <div style="clear: both;"></div><br/>
        <? endif; ?>
        <?
        if (CSaleLocation::isLocationProEnabled()):?>
            <script>

                (window.top.BX || BX).saleOrderAjax.addPropertyDesc(<?=CUtil::PhpToJSObject(array(
                    'id' => intval($arProperty["ID"]),
                    'attributes' => $propertyAttributes
                ))?>);

            </script>
        <?endif ?>
        <?
    }
}
?>