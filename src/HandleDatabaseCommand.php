<?php

namespace Composer\Litepress;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HandleDatabaseCommand extends Command
{

    public function __construct()
    {
        parent::__construct('project:database');
    }

    protected function configure(): void
    {
        $this->setDescription('Initialize the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if(!file_exists('public/content/db.php')) {
            @copy('public/content/plugins/sqlite-database-integration/db.copy','public/content/db.php');
        }

        return Command::SUCCESS;
    }
}