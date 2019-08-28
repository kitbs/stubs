<?php

namespace Stub\Console;

use Stub\Stub;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Quick extends Command
{
    public $commandName = 'quick';

    protected function configure()
    {
        $this->setName($this->commandName);
        $this->setDescription("Clone a folder interactively");
        $this->addArgument('source', InputArgument::REQUIRED, 'What are we stubbing?');
        $this->addArgument('output', InputArgument::REQUIRED, 'Where are we outputing?');
    }

    protected function execute(InputInterface $i, OutputInterface $o)
    {
        $io = new SymfonyStyle($i, $o);
        $io->newLine();
        $io->newLine();
        $io->title('Quick Clone');
        $io->text('Generate a clone of a directory');
        $io->newLine();
        $io->text('<comment>(press enter/return to proceed to the next step)</comment>');
        $io->newLine();
        $io->newLine();

        $searches = [];
        $replaces = [];
        $continue = true;
        $helper = $this->getHelper('question');

        while ($continue) {
            $question = new Question('Search: ');
            $searches[] = $helper->ask($i, $o, $question);

            $question = new Question('Replace: ');
            $replaces[] = $helper->ask($i, $o, $question);

            $question = new Question('Another? (y,n)', 'y');
            $continue = $helper->ask($i, $o, $question);
            $continue = ['y' => true, 'n' => false][strtolower($continue)];

            $io->newLine();
        }

        $staged = uniqid('stub_');
        $source = $i->getArgument('source');
        $output = $i->getArgument('output');

        (new Stub)->source($source)->output($staged)->create(array_flip($searches));
        (new Stub)->source($staged)->output($output)->render($replaces);

        $this->removeDirectoryRecursively($staged);

        $o->writeLn("<info>Created:</info> <comment>$output</comment>");
    }

    public function removeDirectoryRecursively($dir)
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $remove = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $remove($fileinfo->getRealPath());
        }

        rmdir($dir);
    }
}
