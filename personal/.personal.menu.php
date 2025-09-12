<?
$aMenuLinks = Array(
	Array(
		"Система привилегий",
        SITE_DIR ."personal/loyalty/",
		Array(),
		Array(),
		""
	),
	Array(
		"Заказы",
        SITE_DIR . "personal/orders/",
		Array(),
        Array(),
		""
	),
  	Array(
		"Избранное",
        SITE_DIR . "personal/favorites/",
		Array(),
        array("IS_FAVORITES" => true),
		""
	),
	Array(
		"Мои данные",
        SITE_DIR . "personal/private/",
		Array(),
		Array(),
		""
	),
	Array(
		"Выйти",
        SITE_DIR . "?logout=yes",
		Array(),
		Array(),
		""
	)
);
?>
