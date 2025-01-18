<?php

namespace Composer\Litepress;

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
            $app->add(new HandleCreateProjectCommand);
            $app->run(new ArrayInput(['command' => 'project:setup']), new ConsoleOutput());
        } catch (\Exception $e) {
            $event->getIO()->writeError($e->getMessage());
        }
    }

    public static function handleCleanup(Event $event): void
    {
        try {
            $app = self::getApplication();
            $app->add(new HandleCleanupCommand($event));
            $app->run(new ArrayInput(['command' => 'project:cleanup']), new ConsoleOutput());
        } catch (\Exception $e) {
            $event->getIO()->writeError($e->getMessage());
        }
    }

    public static function handleDatabase(Event $event): void
    {
        try {
            $app = self::getApplication();
            $app->add(new HandleDatabaseCommand($event));
            $app->run(new ArrayInput(['command' => 'project:database']), new ConsoleOutput());
        } catch (\Exception $e) {
            $event->getIO()->writeError($e->getMessage());
        }
    }

    public static function handleWordPressInstallation(Event $event): void
    {
        try {
            $app = self::getApplication();
            $app->add(new HandleWordPressInstallationCommand($event));
            $app->run(new ArrayInput(['command' => 'project:install']), new ConsoleOutput());
        } catch (\Exception $e) {
            $event->getIO()->writeError($e->getMessage());
        }
    }

    public static function handleThemeInstallation(Event $event): void
    {
        try {
            $app = self::getApplication();
            $app->add(new HandleThemeInstallationCommand($event));
            $app->run(new ArrayInput(['command' => 'project:theme']), new ConsoleOutput());
        } catch (\Exception $e) {
            $event->getIO()->writeError($e->getMessage());
        }
    }

    public static function handleGeneratingWPCliConfigFile(Event $event): void
    {
        try {
            $app = self::getApplication();
            $app->add(new HandleGeneratingWPCliConfigFileCommand($event));
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