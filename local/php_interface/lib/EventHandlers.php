<?php

namespace Level44;


use Bitrix\Main\EventManager;

class EventHandlers
{
    /** @var $instance  EventManager */
    private static $instance = null;

    public static function register()
    {
        if (!self::$instance) {
            self::$instance = EventManager::getInstance();
        }
    }

    private static function addEventHandler($moduleId, $eventType)
    {
        if (!$moduleId || !$eventType || !self::$instance) {
            return false;
        }
        return self::$instance->addEventHandler(
            $moduleId,
            $eventType,
            [
                self::class,
                $eventType . "Handler"
            ]
        );
    }
}
