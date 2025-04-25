<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Personal Data Processing Policy");
?>
	<div class="row">
		<div class="col-lg-3 d-none d-lg-block">
			<div class="nav-aside">
				<? $APPLICATION->IncludeComponent(
					"bitrix:menu",
					"static_pages",
					Array(
						"ROOT_MENU_TYPE" => "to_customers",
						"MAX_LEVEL" => "1",
						"CHILD_MENU_TYPE" => "top",
						"USE_EXT" => "N",
						"DELAY" => "N",
						"ALLOW_MULTI_SELECT" => "N",
						"MENU_CACHE_TYPE" => "N",
						"MENU_CACHE_TIME" => "3600",
						"MENU_CACHE_USE_GROUPS" => "Y",
						"MENU_CACHE_GET_VARS" => ""
					)
				); ?>
			</div>
		</div>
		<div class="col-lg-9">
			<h1 class="article__title"><?=$APPLICATION->GetTitle()?></h1>
			<? $APPLICATION->IncludeComponent(
				"bitrix:main.include",
				"",
				array(
					"AREA_FILE_SHOW" => "file",
					"PATH" => SITE_DIR . "include/static/personal.php",
				),
				false
			); ?>
		</div>
	</div>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>