<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
} ?>

<? if ($arResult["SITES"]): ?>
	<li class="nav-item">
		<? foreach ($arResult["SITES"] as $key => $arSite): ?>
			<button class="btn btn-link header__btn-link"
			        onclick="window.location.href='<?= $arSite["DIR"] . $arResult["CURRENT_DIR"] ?>'"
			        type="button">
				<?= strtoupper($arSite["LANG"]) ?>
			</button>
		<? endforeach; ?>
	</li>
<? endif; ?>