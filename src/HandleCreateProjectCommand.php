<?php

namespace Composer\Litepress;

use Composer\Util\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class HandleCreateProjectCommand extends Command
{
    protected array $requiredInfo = [
        'site-url' => [
            'env' => 'WP_HOME',
            'question' => 'What is your site URL?',
            'default' => 'http://litepress.test'
        ],
        'admin-email' => [
            'env' => 'ADMIN_EMAIL',
            'question' => 'What is the admin email?',
            'default' => 'admin@example.com'
        ],
        'site-title' => [
            'env' => 'SITE_TITLE',
            'question' => 'What is your site title?',
            'default' => 'WordPress'
        ],
        'admin-user' => [
            'env' => 'ADMIN_USER',
            'question' => 'What is the admin username?',
            'default' => 'admin'
        ],
        'admin-password' => [
            'env' => 'ADMIN_PASSWORD',
            'question' => 'What is the admin password?',
            'default' => 'password'
        ]
    ];

    protected Filesystem $fs;

    public function __construct()
    {
        parent::__construct('project:setup');
        $this->fs = new Filesystem;
    }

    protected function configure(): void
    {
        $this->setDescription('Gather initial project setup information');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $projectInfo = [];

        do {
            $output->writeln('<info>Setting up WordPress project...</info>');

            foreach ($this->requiredInfo as $key => $info) {
                // Check environment variable first
                $value = getenv($info['env']);

                if (!$value) {
                    $question = new Question(
                        sprintf('<question>%s</question> [<comment>%s</comment>]: ', $info['question'], $info['default']),
                        $info['default']
                    );

                    if ($key === 'admin-password') {
                        $question->setHidden(true)->setHiddenFallback(false);
                    }

                    $value = $helper->ask($input, $output, $question);
                } else {
                    $output->writeln(sprintf('Using %s from environment: %s', $key, $value));
                }

                $projectInfo[$key] = $value;
            }

            // Display gathered information
            $output->writeln('<info>Project will be set up with the following information:</info>');
            foreach ($projectInfo as $key => $value) {
                if ($key !== 'admin-password') {
                    $output->writeln(sprintf('  %s: %s', ucwords(str_replace('-', ' ', $key)), $value));
                }
            }

            // Ask for confirmation
            $confirmQuestion = new ConfirmationQuestion(
                '<question>Do you want to proceed with these settings? (y/N)</question> ',
                false
            );

            if (!$helper->ask($input, $output, $confirmQuestion)) {
                $retryQuestion = new ConfirmationQuestion(
                    '<question>Would you like to update these values? (y/N)</question> ',
                    false
                );

                if (!$helper->ask($input, $output, $retryQuestion)) {
                    $output->writeln('<e>Setup cancelled by user. Terminating installation.</e>');
                    exit(1); // Terminate the entire process
                }

                // Clear projectInfo to start over
                $projectInfo = [];
                $output->writeln('');
                continue;
            }
            break;
        } while (true);

        // Update .env file
        try {
            $this->updateEnvFile($projectInfo, $output);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<e>Failed to update .env file: %s</e>', $e->getMessage()));
            exit(1); // Terminate on error
        }

        return Command::SUCCESS;
    }

    private function updateEnvFile(array $projectInfo, OutputInterface $output): void
    {
        if (!file_exists('.env.example')) {
            throw new \RuntimeException('.env.example file not found');
        }

        // Read the template
        $envContent = file_get_contents('.env.example');

        // Update the values
        $replacements = [
            'WP_HOME' => $projectInfo['site-url'],
            'WP_SITEURL' => $projectInfo['site-url'] . '/wp',
            'APP_ENV' => 'local',
            'ADMIN_EMAIL' => $projectInfo['admin-email'],
            'SITE_TITLE' => $projectInfo['site-title'],
            'ADMIN_USER' => $projectInfo['admin-user'],
            'ADMIN_PASSWORD' => $projectInfo['admin-password']
        ];

        // Generate WordPress salts
        $salts = [
            'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY',
            'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT'
        ];

        foreach ($salts as $salt) {
            $replacements[$salt] = $this->generateSalt();
        }

        // Perform the replacements
        foreach ($replacements as $key => $value) {
            // First check if the key exists in the file
            if (strpos($envContent, $key . '=') !== false) {
                // Update existing key
                $pattern = sprintf('/%s=.*$/m', preg_quote($key, '/'));
                $replacement = sprintf('%s=\'%s\'', $key, $value);
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                // Add new key
                $envContent .= PHP_EOL . sprintf('%s=\'%s\'', $key, $value);
            }
        }

        // Write the updated content to .env
        if (file_put_contents('.env', $envContent) === false) {
            throw new \RuntimeException('Failed to write to .env file');
        }

        // Load the new environment variables
        $envLines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($envLines as $line) {
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim(trim($value), "'\"");
                putenv("$key=$value");
            }
        }

        $output->writeln('<info>✓ .env file created with provided settings</info>');
        $output->writeln('<info>✓ Environment variables updated</info>');
    }

    private function generateSalt(): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
        $length = 64;
        $salt = '';
        
        for ($i = 0; $i < $length; $i++) {
            $salt .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $salt;
    }
}