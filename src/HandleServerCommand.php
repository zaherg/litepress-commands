<?php

namespace Composer\Litepress;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class HandleServerCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('project:serve')
            ->setDescription('Start PHP development server')
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Port number to run the server on',
                '8000'
            )
            ->addOption(
                'host',
                'H',
                InputOption::VALUE_OPTIONAL,
                'Host to run the server on',
                'localhost'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $host = $input->getOption('host');
        $port = $input->getOption('port');
        $docRoot = getcwd() . '/public';

        if (!is_dir($docRoot)) {
            $output->writeln("<error>Error: Directory 'public' not found!</error>");
            return Command::FAILURE;
        }

        $process = new Process(['php', '-S', "{$host}:{$port}", '-t', $docRoot]);
        $process->setTimeout(null);
        
        $output->writeln("<info>Starting server: http://{$host}:{$port}</info>");
        $output->writeln("<comment>Document root is: {$docRoot}</comment>");
        $output->writeln("<comment>Press Ctrl+C to stop the server</comment>");

        try {
            $process->setTty(true);