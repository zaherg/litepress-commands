<?php

namespace Composer\Litepress;

use Composer\Litepress\Commands\Cleanup;
use Composer\Litepress\Commands\CreateProject;
use Composer\Litepress\Commands\Database;
use Composer\Litepress\Commands\GenerateWPCliConfig;
use Composer\Litepress\Commands\InstallTheme;
use Composer\Litepress\Commands\InstallWordPress;
use Composer\Script\Event;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class ScriptHandler
{
    private static ?Application $app = null;

    private static function getApplication(): Application
    {
        if (self::$app === null) {
            self::$app = new Application('Litepress');
            self::$app->setAutoExit(false);
        }

        return self::$app;
    }

    public static function handleCreateProject(Event $event): void
    {
        try {
            $app = self::getApplication();
            $app->add(new CreateProject());
            $app->run(new ArrayInput(['command' => 'project:setup']), new ConsoleOutput());
        } catch (\Exception $e) {
            $event->getIO()->writeError($e->getMessage());
        }
    }

    public static function handleCleanup(Event $event): void
    {
        try {
            $app = self::getApplication();
            $app->add(new Cleanup($event));
            $app->run(new ArrayInput(['command' => 'project:cleanup']), new ConsoleOutput());
        } catch (\Exception $e) {
            $event->getIO()->writeError($e->getMessage());
        }
    }

    public static function handleDatabase(Event $event): void
    {
        try {
            $app = self::getApplication();
            $app->add(new Database($event));
            $app->run(new ArrayInput(['command' => 'project:database']), new ConsoleOutput());
        } catch (\Exception $e) {
            $event->getIO()->writeError($e->getMessage());
        }
    }

    public static function handleWordPressInstallation(Event $event): void
    {
        try {
            $app = self::getApplication();
            $app->add(new InstallWordPress($event));
            $app->run(new ArrayInput(['command' => 'project:install']), new ConsoleOutput());
        } catch (\Exception $e) {
            $event->getIO()->writeError($e->getMessage());
        }
    }

    public static function handleThemeInstallation(Event $event): void
    {
        try {
            $app = self::getApplication();
            $app->add(new InstallTheme($event));
            $app->run(new ArrayInput(['command' => 'project:theme']), new ConsoleOutput());
        } catch (\Exception $e) {
            $event->getIO()->writeError($e->getMessage());
        }
    }

    public static function handleGeneratingWPCliConfigFile(Event $event): void
    {
        try {
            $app = self::getApplication();
            $app->add(new GenerateWPCliConfig($event));
            $app->run(new ArrayInput(['command' => 'project:wpcli']), new ConsoleOutput());
        } catch (\Exception $e) {
            $event->getIO()->writeError($e->getMessage());
        }
    }

    public static function handleReinstall(Event $event): void
    {
        try {
            self::handleDatabase($event);
            self::handleCleanup($event);
            self::handleWordPressInstallation($event);
            self::handleThemeInstallation($event);
            self::handleGeneratingWPCliConfigFile($event);
        } catch (\Exception $e) {
            $event->getIO()->writeError($e->getMessage());

            throw $e;
        }
    }
}
