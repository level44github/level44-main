<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
IncludeModuleLangFile(__FILE__);

$psTitle = GetMessage("VAMPIRUS.YANDEX_TITLE");
$psDescription = GetMessage("VAMPIRUS.YANDEX_DESCRIPTION");

$arPSCorrespondence = array(
		"ORDER_ID" => array(
				"NAME" => GetMessage("VAMPIRUS.YANDEX_ORDER_ID"),
				"DESCR" => GetMessage("VAMPIRUS.YANDEX_ORDER_ID_DESCR"),
				"VALUE" => "ID",
				"TYPE" => "ORDER",
				'DEFAULT' => array(
					'PROVIDER_KEY' => 'ORDER',
					'PROVIDER_VALUE' => 'ID'
				),
				"SORT"	=> -3,
			),
		"SHOULD_PAY" => array(
				"NAME" => GetMessage("VAMPIRUS.YANDEX_SHOULD_PAY"),
				"DESCR" => GetMessage("VAMPIRUS.YANDEX_SHOULD_PAY_DESCR"),
				"VALUE" => "SHOULD_PAY",
				"TYPE" => "ORDER",
				'DEFAULT' => array(
					'PROVIDER_KEY' => 'PAYMENT',
					'PROVIDER_VALUE' => 'SUM'
				),
				"SORT"	=> -2,
			),
		"CURRENCY" => array(
				"NAME" => GetMessage("VAMPIRUS.YANDEX_CURRENCY"),
				"DESCR" => GetMessage("VAMPIRUS.YANDEX_CURRENCY_DESCR"),
				"VALUE" => "CURRENCY",
				"TYPE" => "ORDER",
				'DEFAULT' => array(
					'PROVIDER_KEY' => 'PAYMENT',
					'PROVIDER_VALUE' => 'CURRENCY'
				),
				"SORT"	=> -1,
			),
		"SUM_LIMIT" => array(
				"NAME" => GetMessage("VAMPIRUS.YANDEX_LIMIT"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"DOMAIN" => array(
				"NAME" => GetMessage("VAMPIRUS.YANDEX_DOMAIN"),
				"DESCR" => GetMessage("VAMPIRUS.YANDEX_DOMAIN_DESCR",Array('#DOMAIN#'=>$_SERVER['SERVER_NAME'])),
				"VALUE" => "",
				"TYPE" => ""
			),
		"CARD" => array(
				"NAME" => GetMessage("VAMPIRUS.YANDEX_CARD"),
				"DESCR" => GetMessage("VAMPIRUS.YANDEX_CARD_DESCR"),
				"VALUE" => array(
					"0"=>array('NAME' =>GetMessage("VAMPIRUS.YANDEX_CARD_WALLET_OPTION")),
					"1"=>array('NAME' =>GetMessage("VAMPIRUS.YANDEX_CARD_CARD_OPTION")),
					"2"=>array('NAME' =>GetMessage("VAMPIRUS.YANDEX_CARD_MOBILE_OPTION")),
					),
				"TYPE" => "SELECT"
			),
		"PAYMENT_STATUS" => array(
				"NAME" => GetMessage("VAMPIRUS.YANDEX_PAYMENT_STATUS"),
				"DESCR" => GetMessage("VAMPIRUS.YANDEX_PAYMENT_STATUS_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"PAYMENT_MESSAGE" => array(
				"NAME" => GetMessage("VAMPIRUS.YANDEX_PAYMENT_MESSAGE"),
				"DESCR" => GetMessage("VAMPIRUS.YANDEX_PAYMENT_MESSAGE_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),

	);
?>