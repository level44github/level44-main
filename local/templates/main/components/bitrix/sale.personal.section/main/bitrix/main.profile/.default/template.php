<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

/** @var array $arResult */

ShowError($arResult["strProfileError"]);

if (($arResult['DATA_SAVED'] ?? 'N') === 'Y') {
    ShowNote(Loc::getMessage('PROFILE_DATA_SAVED'));
}

$availableClothesSizes = array_map(fn($item) => $item['ID'], (array)$arResult["CLOTHES_SIZE_REF"]);
$availableShoesSizes = array_map(fn($item) => $item['ID'], (array)$arResult["SHOES_SIZE_REF"]);

['UF_CLOTHES_SIZE' => $userClothesSize, 'UF_SHOES_SIZE' => $userShoesSize] = $arResult['USER_PROPERTIES']['DATA'];
?>
<div class="profile profile--personal-info">
    <div class="profile__title"><?= Loc::getMessage('PROFILE_TITLE') ?></div>
    <form action="<?= POST_FORM_ACTION_URI ?>" method="POST" class="profile-form" action="<?= POST_FORM_ACTION_URI ?>"
          enctype="multipart/form-data" role="form">
        <?= $arResult["BX_SESSION_CHECK"] ?>
        <input type="hidden" name="lang" value="<?= LANG ?>"/>
        <input type="hidden" name="ID" value="<?= $arResult["ID"] ?>"/>
        <input type="hidden" name="LOGIN" value="<?= $arResult["arUser"]["LOGIN"] ?>"/>

        <div class="form-group">
            <input class="form-control js-form__control" type="text" id="form-firstName" maxlength="50" required
                   placeholder="<?= Loc::getMessage('NAME') ?>" value="<?= $arResult["arUser"]["NAME"] ?>" name="NAME">
        </div>
        <div class="form-group">
            <input class="form-control js-form__control" type="text" id="form-lastName" name="LAST_NAME" maxlength="50"
                   required placeholder="<?= Loc::getMessage('LAST_NAME') ?>"
                   value="<?= $arResult["arUser"]["LAST_NAME"] ?>">
        </div>
        <div class="form-group">
            <input class="form-control js-form__control" type="text" id="form-secondName" name="SECOND_NAME"
                   maxlength="50"
                   placeholder="<?= Loc::getMessage('SECOND_NAME') ?>"
                   value="<?= $arResult["arUser"]["SECOND_NAME"] ?>">
        </div>
        <div class="form-group">
            <input class="form-control js-form__control js-form__birthdate" type="text"
                   name="PERSONAL_BIRTHDAY" id="form-birthdate"
                   placeholder="<?= Loc::getMessage('BIRTHDAY') ?>"
                   data-placeholder="<?= Loc::getMessage('BIRTHDAY_MASK') ?>"
                   value="<?= $arResult["arUser"]["PERSONAL_BIRTHDAY"] ?>">
            <div class="invalid-feedback"></div>
        </div>
        <div class="form-group">
            <div class="select-wrapper">
                <select class="form-control js-form__control" required id="form-gender" name="PERSONAL_GENDER">
                    <option value="" disabled
                        <? if (!in_array($arResult["arUser"]["PERSONAL_GENDER"], [
                            'M',
                            'F'
                        ])) echo ' selected' ?>><?= Loc::getMessage('GENDER') ?></option>
                    <option value="M"
                        <? if ($arResult["arUser"]["PERSONAL_GENDER"] === 'M') echo ' selected' ?>><?= Loc::getMessage('GENDER_M') ?></option>
                    <option value="F"
                        <? if ($arResult["arUser"]["PERSONAL_GENDER"] === 'F') echo ' selected' ?>><?= Loc::getMessage('GENDER_F') ?></option>
                </select>
                <svg class="icon icon-arrow-down dropdown-arrow">
                    <use xlink:href="#arrow-down"></use>
                </svg>
            </div>
        </div>
        <div class="form-group">
            <input class="form-control js-form__control js-form__phone" type="text" id="form-phone"
                   placeholder="<?= Loc::getMessage('PHONE') ?>" name="PERSONAL_PHONE"
                   value="<?= $arResult["arUser"]["PERSONAL_PHONE"] ?>">
            <div class="invalid-feedback"></div>
        </div>
        <div class="form-group">
            <input class="form-control js-form__control js-form__email" type="email" id="form-email" name="EMAIL"
                   placeholder="<?= Loc::getMessage('EMAIL') ?>" maxlength="50"
                   value="<?= $arResult["arUser"]["EMAIL"] ?>">
            <div class="invalid-feedback"></div>
        </div>
        <? if (!empty($arResult["CLOTHES_SIZE_REF"])): ?>
            <div class="form-group">
                <div class="select-wrapper">
                    <select class="form-control js-form__control" id="form-size" name="UF_CLOTHES_SIZE">
                        <option <? if (!in_array($userClothesSize['VALUE'], $availableClothesSizes)) echo ' selected'; ?>
                                value="" disabled><?= Loc::getMessage('CLOTHING_SIZE') ?></option>
                        <? foreach ($arResult["CLOTHES_SIZE_REF"] as $size): ?>
                            <option
                                <? if ($userClothesSize['VALUE'] === $size['ID']) echo ' selected'; ?>
                                    value="<?= $size['ID'] ?>"><?= $size['UF_NAME'] ?></option>
                        <? endforeach; ?>
                    </select>
                    <svg class="icon icon-arrow-down dropdown-arrow">
                        <use xlink:href="#arrow-down"></use>
                    </svg>
                </div>
            </div>
        <? endif; ?>
        <? if (!empty($arResult["SHOES_SIZE_REF"])): ?>
            <div class="form-group">
                <div class="select-wrapper">
                    <select class="form-control js-form__control" id="form-shoe-size" name="UF_SHOES_SIZE">
                        <option <? if (!in_array($userShoesSize['VALUE'], $availableShoesSizes)) echo ' selected'; ?>
                                value="" disabled><?= Loc::getMessage('SHOE_SIZE') ?></option>
                        <? foreach ($arResult["SHOES_SIZE_REF"] as $size): ?>
                            <option <? if ($userShoesSize['VALUE'] === $size['ID']) echo ' selected'; ?>
                                    value="<?= $size['ID'] ?>"><?= $size['UF_NAME'] ?></option>
                        <? endforeach; ?>
                    </select>
                    <svg class="icon icon-arrow-down dropdown-arrow">
                        <use xlink:href="#arrow-down"></use>
                    </svg>
                </div>
            </div>
        <? endif; ?>
        <button class="btn btn-dark btn-block" type="submit" name="save"
                value="Y"><?= Loc::getMessage('SAVE') ?></button>
        <div class="privacy-text"><?= Loc::getMessage('PRIVACY_TEXT', ['#SITE_DIR#' => SITE_DIR]) ?></div>
    </form>
</div>

<script type="text/javascript">
    var fieldRequiredMes = <?=Json::encode(Loc::getMessage('FIELD_REQUIRED_MESSAGE'))?>;
    var fieldIncorrectMes = <?=Json::encode(Loc::getMessage('FIELD_INCORRECT_MESSAGE'))?>;
</script>