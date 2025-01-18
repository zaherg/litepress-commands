<?php

namespace Composer\Litepress\Commands;

use Composer\Litepress\Utils;
use Composer\Script\Event;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class InstallWordPress extends Command
{
    protected Event $event;

    public function __construct(Event $event)
    {
        parent::__construct('project:install');
        Utils::loadEvn($event);
        $this->event = $event;
    }

    protected function configure(): void
    {
        $this->setDescription('Install WordPress with configured settings');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $adminUser = getenv('ADMIN_USER') ?: $_ENV['ADMIN_USER'];
        $adminPassword = getenv('ADMIN_PASSWORD') ?: $_ENV['ADMIN_PASSWORD'];
        $adminEmail = getenv('ADMIN_EMAIL') ?: $_ENV['ADMIN_EMAIL'];
        $siteUrl = getenv('WP_HOME') ?: $_ENV['WP_HOME'];
        $siteTitle = getenv('SITE_TITLE') ?: $_ENV['SITE_TITLE'];


        // Validate required environment variables
        $requiredVars = [
           'ADMIN_USER' => $adminUser,
           'ADMIN_PASSWORD' => $adminPassword,
           'ADMIN_EMAIL' => $adminEmail,
           'WP_HOME' => $siteUrl,
           'SITE_TITLE' => $siteTitle,
        ];

        $missingVars = array_filter($requiredVars, fn ($value) => empty($value));

        if (! empty($missingVars)) {
            $output->writeln('<fg=red>Missing required environment variables: </>');
            foreach (array_keys($missingVars) as $var) {
                $output->writeln("<fg=red>- {$var}</>");
            }
            exit(1);
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
            '--skip-email',
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

            if (! $process->isSuccessful()) {
                throw new \RuntimeException('WordPress installation failed');
            }

            $this->removePasswordFromEnv($output);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<e>' . $e->getMessage() . '</e>');
            exit(1);
        }
    }

    private function removePasswordFromEnv(OutputInterface $output): void
    {
        if (! file_exists('.env')) {
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
