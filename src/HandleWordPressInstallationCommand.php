<?php

namespace Composer\Litepress;

use Composer\Script\Event;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class HandleWordPressInstallationCommand extends Command
{
    protected Event $event;

    public function __construct(Event $event)
    {
        parent::__construct('project:install');
        $this->event = $event;
    }

    protected function configure(): void
    {
        $this->setDescription('Install WordPress with configured settings');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $adminUser = getenv('ADMIN_USER');
        $adminPassword = getenv('ADMIN_PASSWORD');
        $adminEmail = getenv('ADMIN_EMAIL');
        $siteUrl = getenv('WP_HOME');
        $siteTitle = getenv('SITE_TITLE');

        // Validate required environment variables
        if (!$adminUser || !$adminPassword || !$adminEmail || !$siteUrl || !$siteTitle) {
            $output->writeln('<e>Missing required environment variables. Please run project:setup first.</e>');
            return Command::FAILURE;
        }

        // Get the bin directory from Composer configuration
        $binDir = $this->event->getComposer()->getConfig()->get('bin-dir');
        $wpBinary = $binDir . DIRECTORY_SEPARATOR . 'wp';

        $process = new Process([
            $wpBinary,
            'core',
            'install',
            '--path=public/wp',
            '--admin_user=' . $adminUser,
            '--admin_password=' . $adminPassword,
            '--admin_email=' . $adminEmail,
            '--url=' . $siteUrl,
            '--title=' . $siteTitle,
            '--skip-email'
        ]);

        try {
            $process->setTty(true);
        } catch (\RuntimeException $e) {
            // TTY not available, continue without it
        }

        try {
            $process->run(function ($type, $buffer) use ($output) {
                if (strpos($buffer, 'Success:') !== false) {
                    $output->writeln('<info>✓ WordPress installed successfully</info>');
                } else {
                    $output->write($buffer);
                }
            });

            if (!$process->isSuccessful()) {
                throw new \RuntimeException('WordPress installation failed');
            }

            $this->removePasswordFromEnv($output);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<e>' . $e->getMessage() . '</e>');
            return Command::FAILURE;
        }
    }

    private function removePasswordFromEnv(OutputInterface $output): void
    {
        if (!file_exists('.env')) {
            return;
        }

        $envContent = file_get_contents('.env');
        $pattern = '/^ADMIN_PASSWORD=.*$/m';
        $envContent = preg_replace($pattern, 'ADMIN_PASSWORD=', $envContent);

        if (file_put_contents('.env', $envContent) === false) {
            $output->writeln('<e>Failed to update .env file to remove password</e>');
            return;
        }

        // Update current environment
        putenv('ADMIN_PASSWORD=');
    }
}