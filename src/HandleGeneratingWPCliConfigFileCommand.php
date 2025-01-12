<?php

namespace Composer\Litepress;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class HandleGeneratingWPCliConfigFileCommand extends Command
{
    private array $config = [
        'path' => 'public/wp',
        'url' => '${WP_HOME}',
        'config create' => [
            'dbname' => 'wordpress',
            'dbuser' => 'root',
            'dbpass' => '',
            'dbhost' => 'localhost',
            'dbprefix' => 'wp_',
            'extra-php' => "define( 'DB_FILE', dirname( ABSPATH ) . '/content/database/.ht.sqlite' );\ndefine( 'DB_DIR', dirname( ABSPATH ) . '/content/database/' );"
        ],
        'disabled_commands' => [
            'db drop',
            'db export',
            'db import'
        ],
        'server' => [
            'docroot' => 'public'
        ]
    ];

    public function __construct()
    {
        parent::__construct('project:wpcli');
    }

    protected function configure(): void
    {
        $this->setDescription('Generate wp-cli.local.yml configuration file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            // Generate YAML content
            $yaml = Yaml::dump($this->config, 4, 2);

            // Write the file
            if (file_put_contents('wp-cli.local.yml', $yaml) === false) {
                throw new \RuntimeException('Failed to write wp-cli.local.yml file');
            }

            $output->writeln('<info>✓ WP-CLI configuration generated</info>');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $output->writeln('<e>' . $e->getMessage() . '</e>');
            return Command::FAILURE;
        }
    }
}