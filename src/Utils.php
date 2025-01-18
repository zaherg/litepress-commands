<?php

namespace Composer\Litepress;

use Composer\Script\Event;

class Utils
{
    private static ?string $baseDir = null;

    public static function getBaseDir(Event $event): string
    {
        if (self::$baseDir === null) {
            self::$baseDir = dirname($event->getComposer()->getConfig()->get('vendor-dir'));
        }

        return self::$baseDir;
    }

    public static function loadEvn(Event $event): void
    {
        if(file_exists(self::getBaseDir($event) . '/.env')) {
            \Dotenv\Dotenv::createMutable(self::getBaseDir($event))->load();
        }
    }
}