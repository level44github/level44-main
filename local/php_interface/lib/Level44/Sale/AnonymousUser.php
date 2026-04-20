<?php

namespace Level44\Sale;

use Bitrix\Main\Security\Random;

class AnonymousUser
{
    /**
     * Аналог CSaleUser::GetAnonymousUserID(), но ВСЕГДА создаёт нового пользователя.
     * Возвращает ID созданного пользователя или 0 при неудаче.
     */
    public static function createNewUserId(string $siteId = SITE_ID): int
    {
        if (!class_exists('\CUser')) {
            return 0;
        }

        $password = self::randomString(20);
        $login = 'anon_' . self::randomString(8) . '_' . (string)time();

        $fields = [
            'LOGIN' => $login,
            'NAME' => 'Anonymous',
            'LAST_NAME' => '',
            'SECOND_NAME' => 'preorder',
            'ACTIVE' => 'Y',
            'LID' => $siteId,
            'PASSWORD' => $password,
            'CONFIRM_PASSWORD' => $password,
            // EMAIL часто требуется в кастомных правилах/интеграциях, поэтому задаём уникальный технический.
            'EMAIL' => $login . '@mail.preorder',
        ];

        $user = new \CUser();
        $id = (int)$user->Add($fields);

        return $id > 0 ? $id : 0;
    }

    private static function randomString(int $length): string
    {
        if (class_exists(Random::class)) {
            return Random::getString($length);
        }

        return substr(bin2hex(random_bytes(max(1, (int)ceil($length / 2)))), 0, $length);
    }
}

