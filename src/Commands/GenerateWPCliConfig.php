<?php

namespace Composer\Litepress\Commands;

use Composer\Litepress\Utils;
use Composer\Script\Event;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class GenerateWPCliConfig extends Command
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
            'extra-php' => "define( 'DB_FILE', dirname( ABSPATH ) . '/content/database/.ht.sqlite' );\ndefine( 'DB_DIR', dirname( ABSPATH ) . '/content/database/' );",
        ],
        'disabled_commands' => [
            'db drop',
            'db export',
            'db import',
        ],
        'server' => [
            'docroot' => 'public',
        ],
    ];

    protected Event $event;

    public function __construct(Event $event)
    {
        parent::__construct('project:wpcli');
        $this->event = $event;
        Utils::loadEvn($event);
    }

    protected function configure(): void
    {
        $this->setDescription('Generate wp-cli.local.yml configuration file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->config['url'] = str_replace('${WP_HOME}', getenv('WP_HOME') ?: $_ENV['WP_HOME'], $this->config['url']);

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
            exit(1);
        }
    }
}
