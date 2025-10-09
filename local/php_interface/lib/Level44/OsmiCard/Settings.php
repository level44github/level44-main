<?php

namespace Level44\OsmiCard;

use Bitrix\Main\Config\Option;

/**
 * Класс для работы с настройками OSMI Card
 */
class Settings
{
    const MODULE_ID = 'level44.osmicard';

    /**
     * Получить значение настройки
     * 
     * @param string $name Название настройки
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public static function get(string $name, $default = '')
    {
        return Option::get(self::MODULE_ID, $name, $default);
    }

    /**
     * Установить значение настройки
     * 
     * @param string $name Название настройки
     * @param mixed $value Значение
     * @return void
     */
    public static function set(string $name, $value): void
    {
        Option::set(self::MODULE_ID, $name, $value);
    }

    /**
     * Получить все настройки
     * 
     * @return array
     */
    public static function getAll(): array
    {
        return [
            'enabled' => self::get('enabled', 'N'),
            'api_url' => self::get('api_url', 'https://api.osmicards.com/v2'),
            'api_username' => self::get('api_username', ''),
            'api_password' => self::get('api_password', ''),
            'project_id' => self::get('project_id', ''),
            'template_id' => self::get('template_id', ''),
        ];
    }

    /**
     * Сохранить все настройки
     * 
     * @param array $settings Массив настроек
     * @return void
     */
    public static function setAll(array $settings): void
    {
        foreach ($settings as $key => $value) {
            self::set($key, $value);
        }
    }

    /**
     * Проверить корректность настроек
     * 
     * @return bool
     */
    public static function isValid(): bool
    {
        $settings = self::getAll();
        
        return !empty($settings['api_username']) && 
               !empty($settings['api_password']) &&
               !empty($settings['template_id']) &&
               $settings['enabled'] === 'Y';
    }
}

