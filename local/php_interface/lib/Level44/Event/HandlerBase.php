<?php

namespace Level44\Event;

use Bitrix\Main\EventManager;
use CIBlockProperty;

class HandlerBase
{
    protected static function addEventHandler(
        $moduleId,
        $eventType,
        ?string $class = null,
        ?string $method = null,
        ?int $sort = 100,
        ?bool $compatible = false
    ): void
    {
        $instance = EventManager::getInstance();

        if (!$moduleId || !$eventType || !$instance) {
            return;
        }

        $class = $class ?? static::class;

        if (!$method) {
            $method = $eventType . "Handler";
        }

        if (!method_exists($class, $method)) {
            return;
        }

        if ($compatible) {
            $instance->addEventHandlerCompatible(
                $moduleId,
                $eventType,
                [$class, $method],
                false,
                $sort
            );
        } else {
            $instance->addEventHandler(
                $moduleId,
                $eventType,
                [$class, $method],
                false,
                $sort
            );
        }
    }


    protected static function getPropertyValue($property): mixed
    {
        if (!is_array($property)) {
            $property = [];
        }

        return $property[key($property)]["VALUE"];
    }

    protected static function setPropertyValue(&$property, $value): void
    {
        if (!is_array($property)) {
            return;
        }

        $property[key($property)]["VALUE"] = $value;
    }

    protected static function getProperties(int $iblockId): array
    {
        $res = CIBlockProperty::GetList([], ["IBLOCK_ID" => $iblockId]);

        $properties = [];
        while ($property = $res->GetNext()) {
            $properties[$property["CODE"]] = $property["ID"];
        }

        return $properties;
    }
}