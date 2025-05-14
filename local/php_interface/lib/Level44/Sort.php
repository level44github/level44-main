<?php

namespace Level44;

/**
 * Class Sort
 * @package Level44
 */
class Sort
{
    private $data = [];

    public function __construct(?string $type = null)
    {
        $this->initData($type);
    }

    /**
     * @return void
     */
    private function initData(?string $type = null): void
    {
        $data =& $this->data;
        $data["type"] = $type;
        $data["code"] = $this->getCode();
        $data["field2"] = 'sort';
        $data["order2"] = 'asc';

        switch ($data["code"]) {
            case "price-asc":
                $data["field"] = 'SCALED_PRICE_1';
                $data["order"] = 'asc';
                break;
            case "price-desc":
                $data["field"] = 'SCALED_PRICE_1';
                $data["order"] = 'desc';
                break;
            case "popularity":
                $data["field"] = 'shows';
                $data["order"] = 'desc';
                break;
            case "new":
                $data["field"] = 'created';
                $data["order"] = 'desc';
                break;
            default:
                $data["field"] = 'sort';
                $data["order"] = 'asc';
                $data["code"] = 'default';
                break;
        }

        $data["list"] = [
            [
                "code" => "default",
                "name" => Base::getMultiLang("По умолчанию", "Default"),
            ],
            [
                "code" => "price-asc",
                "name" => Base::getMultiLang("По возрастанию цены", "Price: Low to High"),
            ],
            [
                "code" => "price-desc",
                "name" => Base::getMultiLang("По убыванию цены", "Price: High to Low"),
            ],
            [
                "code" => "popularity",
                "name" => Base::getMultiLang("По популярности", "Popularity"),
            ],
            [
                "code" => "new",
                "name" => Base::getMultiLang("По новизне", "Newest"),
            ],
        ];

        if (!in_array($data["code"], array_map(fn($item) => $item['code'], $data["list"]))) {
            $data["code"] = 'default';
        }

        $data["list"] = array_map(
            fn($item) => array_merge($item, ['selected' => $item["code"] === $data["code"]]),
            $data["list"]
        );

        unset($data);
    }

    /**
     * @param string $fieldName
     * @return string
     */
    public function getValue(string $fieldName): string
    {
        return $this->data[$fieldName] ?: '';
    }

    /**
     * @return array
     */
    public function getList(): array
    {
        return $this->data["list"];
    }

    /**
     * @return string
     */
    public function getCookieName(): string
    {
        switch ($this->data["type"]) {
            case "catalog":
            default:
                return 'catalog-sort';
            case "search":
                return 'search-sort';
        }
    }

    /**
     * @return string|null
     */
    private function getCode(): null|string
    {
        return $_COOKIE[$this->getCookieName()];
    }
}