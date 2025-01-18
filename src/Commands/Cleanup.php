<?php

namespace Composer\Litepress\Commands;

use Composer\Litepress\Utils;
use Composer\Script\Event;
use Composer\Util\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Cleanup extends Command
{
    protected Event $event;
    protected Filesystem $fs;

    public function __construct(Event $event)
    {
        parent::__construct('project:cleanup');
        $this->event = $event;
        $this->fs = new Filesystem();
    }

    protected function configure(): void
    {
        $this->setDescription('Cleanup directories');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (is_dir(Utils::getBaseDir($this->event).'/public/wp/wp-content')) {
            $this->fs->removeDirectory(Utils::getBaseDir($this->event).'/public/wp/wp-content');
        }

        return Command::SUCCESS;
    }
}
