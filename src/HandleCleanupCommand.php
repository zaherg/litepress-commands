<?php

namespace Composer\Litepress;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HandleCleanupCommand extends Command
{

    public function __construct()
    {
        parent::__construct('project:cleanup');
    }

    protected function configure(): void
    {
        $this->setDescription('Cleanup directories');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if(is_dir('public/wp/wp-content')) {
            @unlink('public/wp/wp-content');
            @rmdir('public/wp/wp-content');
        }

        return Command::SUCCESS;
    }
}