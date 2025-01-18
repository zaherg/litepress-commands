<?php

namespace Composer\Litepress\Commands;

use Composer\Litepress\Utils;
use Composer\Script\Event;
use Composer\Util\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Database extends Command
{
    protected Event $event;
    protected Filesystem $fs;

    public function __construct(Event $event)
    {
        parent::__construct('project:database');
        $this->event = $event;
        $this->fs = new Filesystem();
    }

    protected function configure(): void
    {
        $this->setDescription('Initialize the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (! file_exists(Utils::getBaseDir($this->event).'/public/content/db.php')) {
            $this->initDatabase($output);
        } else {
            $this->deleteDatabase($output);
            $this->initDatabase($output);
        }

        return Command::SUCCESS;
    }

    private function deleteDatabase(OutputInterface $output)
    {
        $output->writeln('Deleting the database');
        if (is_dir(Utils::getBaseDir($this->event).'/public/content/database')) {
            $this->fs->removeDirectory(Utils::getBaseDir($this->event).'/public/content/database');
        }

        $this->fs->remove(Utils::getBaseDir($this->event).'/public/content/db.php');
    }

    private function initDatabase(OutputInterface $output)
    {
        $output->writeln('Initializing the database');
        $this->fs->copy(
            Utils::getBaseDir($this->event).'/public/content/plugins/sqlite-database-integration/db.copy',
            Utils::getBaseDir($this->event).'/public/content/db.php'
        );
    }
}
