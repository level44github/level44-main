<?
include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/urlrewrite.php');

use \Bitrix\Main\Localization\Loc;

CHTTP::SetStatus("404 Not Found");
@define("ERROR_404", "Y");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION->SetTitle("404 Not Found"); ?>
<div class="container page-404__container">
    <h1 class="page-404__title"><?= Loc::getMessage("PAGE_NOT_FOUND") ?></h1>
    <a class="btn btn-dark btn__fix-width" href="<?= SITE_DIR ?>"><?= Loc::getMessage("GO_TO_MAIN_PAGE") ?></a>
</div>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
