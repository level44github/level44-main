<?php

namespace Level44\Event;

use Bitrix\Main\EventManager;

class HandlerBase
{
    protected static function addEventHandler($moduleId, $eventType, ?string $class = null): void
    {
        $instance = EventManager::getInstance();

        if (!$moduleId || !$eventType || !$instance) {
            return;
        }

        $class = $class ?? static::class;
        $method = $eventType . "Handler";

        if (!method_exists($class, $method)) {
            return;
        }

        $instance->addEventHandler(
            $moduleId,
            $eventType,
            [$class, $method]
        );
    }
}