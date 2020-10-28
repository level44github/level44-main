<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
require(__DIR__ . '/Builder.php');

global $APPLICATION;
global $USER;

if (!$USER->IsAdmin()) {
    die();
}

$locationsResult = "CODE;PARENT_CODE;TYPE_CODE;NAME.EN.NAME;NAME.RU.NAME;EXT.ZIP.0\n";
$locationsLog = "";

$builder = new Builder();

if ($_GET["buildList"] === "Y") {
    $countries = \Bitrix\Sale\Location\LocationTable::getList(
        [
            "filter" => [
                "TYPE_ID" => 1,
                "NAME.LANGUAGE_ID" => "en",
                "!ID" => \Level44\Base::getSngCountriesId()
            ],
            "select" => ["*", "NAME.NAME"]
        ])->fetchAll();

    foreach ($countries as $country) {
        $enName = $country["SALE_LOCATION_LOCATION_NAME_NAME"];
        if (empty($enName)) {
            $locationsLog .= "bitrix: Country is empty. ID={$country["ID"]}\n";
            continue;
        }

        if ($enName === "UK") {
            $enName = "Kingdom";
        }

        $response = file_get_contents("https://restcountries.eu/rest/v2/name/{$enName}/?fields=alpha2Code;capital");
        $countryData = json_decode($response, true)[0];
        if (empty($countryData["alpha2Code"])) {
            $locationsLog .= "restcountries: Country code is empty. ID={$country["ID"]}\n";
            continue;
        }

        $alphaCode = $countryData["alpha2Code"];

        if (empty($countryData["capital"])) {
            $locationsLog .= "restcountries: Capital is empty. ID={$country["ID"]}\n";
            continue;
        }

        $capital = $countryData["capital"];
        $code = $builder->getCode();
        $zip = $builder->getZipByCity($capital);

        if (empty($zip)) {
            $locationsLog .= "bitrix: Zip is empty. ID={$country["ID"]}\n";
            continue;
        }
        $locationsResult .= "{$code};{$country["CODE"]};CITY;{$capital};$zip\n";

        continue;
    }

    file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/locations.txt", $locationsResult);
    file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/locations.log", $locationsLog);
}

if ($_GET["fix"] === "Y") {
    Builder::fixAstana();
}

die();