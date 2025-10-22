<?
$aMenuLinks = Array(

    Array(
        "Personal account",
        SITE_DIR ."personal/main/",
        Array(),
        Array(),
        ""
    ),
    Array(
        "The privilege system",
        SITE_DIR ."personal/loyalty/",
        Array(),
        Array(),
        ""
    ),
	Array(
		"Orders",
        SITE_DIR . "personal/orders/",
		Array(),
        Array(),
		""
	),
  	Array(
		"Selected",
        SITE_DIR . "personal/favorites/",
		Array(),
        array("IS_FAVORITES" => true),
		""
	),
	Array(
		"My information",
        SITE_DIR . "personal/private/",
		Array(),
		Array(),
		""
	),
	Array(
		"Log out",
        SITE_DIR . "?logout=yes",
		Array(),
		Array(),
		""
	)
);
?>
