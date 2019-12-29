<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if (isset($_REQUEST["backurl"]) && strlen($_REQUEST["backurl"])>0) 
	LocalRedirect($backurl);

$APPLICATION->SetTitle("Login");
?>
<p>You are registered and successfully logged in.</p>

<p><a href="<?=SITE_DIR?>">Return to the homepage</a></p>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>