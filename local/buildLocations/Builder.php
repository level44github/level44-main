<?php

use \Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Location\Name\LocationTable as LocationNameTable;

class Builder
{
    private $lastLocationCode = 1000000000;
    private $addedCodes = [];
    private $zipCodes = array(
        "Kiev" => "01001",
        "Washington, D.C." => "20001",
        "Ottawa" => "K0A",
        "Tirana" => "1031",
        "Andorra la Vella" => "AD500",
        "Vienna" => "1010",
        "Brussels" => "1000",
        "Sofia" => "BG1309",
        "Zagreb" => "10434",
        "Copenhagen" => "1050",
        "Helsinki" => "00100",
        "Paris" => "75000",
        "Berlin" => "10115",
        "Athens" => "10431",
        "Budapest" => "1007",
        "Reykjavík" => "101",
        "Rome" => "00100",
        "Vaduz" => "9490",
        "Luxembourg" => "1111",
        "Skopje" => "1000",
        "Valletta" => "VLT1010",
        "Monaco" => "98000",
        "Amsterdam" => "1008",
        "Oslo" => "0010",
        "Warsaw" => "00-001",
        "Lisbon" => "1000-004",
        "Bucharest" => "010082",
        "Bratislava" => "2412",
        "Ljubljana" => "1501",
        "Dublin" => "94568",
        "Madrid" => "28001",
        "Stockholm" => "10465",
        "Bern" => "3001",
        "London" => "SE1 7PB",
        "Sarajevo" => "71000",
        "Prague" => "15300",
        "Mexico City" => "01139",
        "Jerusalem" => "9103401",
        "Canberra" => "2601",
        "Luanda" => "2177",
        "Wellington" => "5016",
        "Baku" => "1005",
        "Tallinn" => "10111",
        "Tbilisi" => "0108",
        "Riga" => "1069",
        "Vilnius" => "01100",
        "Chișinău" => "2000",
        "Ashgabat" => "744000",
        "Yerevan" => "0815",
        "Dushanbe" => "734",
        "Tashkent" => "100012",
        "Bishkek" => "720000",
    );


    public function getCode()
    {
        while ($this->isCodeBusy()) {
            $this->incCode();
        }

        $this->toAddedCodes();

        return $this->getPreparedCode();
    }

    public function getZipByCity($cityName)
    {
        return (string)$this->zipCodes[$cityName];
    }

    private function getPreparedCode()
    {
        $this->lastLocationCode;
        return str_pad($this->lastLocationCode, 10, "0", STR_PAD_LEFT);
    }

    private function isCodeBusy()
    {
        return !empty(LocationTable::getByCode($this->getPreparedCode())->fetch()) || $this->inAddedCodes();
    }

    private function incCode()
    {
        $this->lastLocationCode++;
    }

    private function toAddedCodes()
    {
        $this->addedCodes[] = $this->getPreparedCode();
    }

    private function inAddedCodes()
    {
        return in_array($this->getPreparedCode(), $this->addedCodes);
    }

    public function createCountry($params)
    {
        if (empty($params["countryNameRu"]) || empty($params["capital"])) {
            return "";
        }

        if (empty($params["countryNameEn"])) {
            return "";
        }

        $countryCode = $this->getCode();
        $result = "{$countryCode};;COUNTRY;{$params["countryNameEn"]};{$params["countryNameRu"]};\n";
        $cityCode = $this->getCode();
        $result .= "{$cityCode};{$countryCode};CITY;{$params["capital"]};;{$params["zip"]}\n";
        return $result;
    }

    public static function fixAstana()
    {
        $city = LocationTable::getList(
            [
                "filter" => [
                    "TYPE_ID" => 5,
                    "NAME.LANGUAGE_ID" => "ru",
                    "PARENT.NAME.LANGUAGE_ID" => "ru",
                    "NAME.NAME" => "Астана",
                ],
                "select" => [
                    "*",
                    "NAME.NAME",
                    "PARENT.NAME.NAME"
                ]
            ])->fetch();


        if ($city && $city["SALE_LOCATION_LOCATION_PARENT_NAME_NAME"] !== "Акмолинская область") {
            $parent = LocationTable::getList(
                [
                    "filter" => [
                        "NAME.LANGUAGE_ID" => "ru",
                        "NAME.NAME" => "Акмолинская область",
                    ],
                    "select" => ["*", "NAME.NAME"]
                ])->fetch();

            $names = LocationNameTable::getList(
                [
                    "filter" => [
                        "LOCATION_ID" => $city["ID"]
                    ]
                ])->fetchAll();

            foreach ($names as $nameEntity) {
                switch ($nameEntity["LANGUAGE_ID"]) {
                    case "ru":
                        $newName = "Нур-Султан (Астана)";
                        break;
                    case "en":
                        $newName = "Nur-Sultan (Astana)";
                        break;
                }

                if ($newName) {
                    $changed = LocationNameTable::update($nameEntity["ID"], ["NAME" => $newName]);

                    if ($changed->isSuccess()) {
                        echo "<br>-Изменено {$nameEntity["LANGUAGE_ID"]} название города Астана<br>";
                    }
                }
            }

            if ($parent) {
                $changed = LocationTable::update($city["ID"], ["PARENT_ID" => $parent["ID"], ""]);

                if ($changed->isSuccess()) {
                    echo "<br>-Изменено расположение города Астана<br>";
                }

                if ($city["SALE_LOCATION_LOCATION_PARENT_NAME_NAME"] === "Астана" && $changed->isSuccess()) {
                    LocationTable::delete($city["PARENT_ID"]);
                }
            }
        }
    }
}