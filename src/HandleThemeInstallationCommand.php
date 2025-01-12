<?php

namespace Composer\Litepress;

use Composer\Script\Event;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class HandleThemeInstallationCommand extends Command
{
    protected Event $event;

    public function __construct(Event $event)
    {
        parent::__construct('project:theme');
        $this->event = $event;
    }

    protected function configure(): void
    {
        $this->setDescription('Install and activate Extendable theme');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            // Get the bin directory from Composer configuration
            $binDir = $this->event->getComposer()->getConfig()->get('bin-dir');
            $wpBinary = $binDir . DIRECTORY_SEPARATOR . 'wp';

            // Activate theme
            $process = new Process([
                $wpBinary,
                'theme',
                'activate',
                'extendable',
                '--path=public/wp',
            ]);

            try {
                $process->setTty(true);
            } catch (\RuntimeException $e) {
                // TTY not available, continue without it
            }

            $process->run(function ($type, $buffer) use ($output) {
                if (strpos($buffer, 'Success:') !== false) {
                    $output->writeln('<info>✓ Theme activated successfully</info>');
                } else {
                    $output->write($buffer);
                }
            });

            if (!$process->isSuccessful()) {
                throw new \RuntimeException('Theme activation failed');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}