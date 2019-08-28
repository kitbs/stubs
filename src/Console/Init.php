<?php

namespace Stub\Console;

use Stub\Stub;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Init extends Command
{
    public $commandName = 'init';

    protected function configure()
    {
        $this->setName($this->commandName);
        $this->setDescription("Create stub.json with values interactively");
        $this->addArgument('file', InputArgument::OPTIONAL, 'Output file name', 'stub.json');
    }

    protected function execute(InputInterface $i, OutputInterface $o)
    {
        $values = [];
        $continue = true;
        $helper = $this->getHelper('question');
        $file = $i->getArgument('file');

        $io = new SymfonyStyle($i, $o);
        $io->newLine();
        $io->newLine();
        $io->title('Initializing Stub Values');
        $io->text('Generate a stub.json file: {"search": "replace"}');
        $io->newLine();
        $io->text('<comment>(press enter/return to proceed to the next step)</comment>');
        $io->newLine();
        $io->newLine();

        while ($continue) {
            $question = new Question('Search: ');
            $search = $helper->ask($i, $o, $question);
            $question = new Question('Replace: ');
            $replace = $helper->ask($i, $o, $question);

            $values[$search] = $replace;

            $question = new Question('Another? (y,n)', 'y');
            $continue = $helper->ask($i, $o, $question);
            $continue = ['y' => true, 'n' => false][strtolower($continue)];

            $io->newLine();
        }

        file_put_contents($file, json_encode($values, JSON_PRETTY_PRINT));

        $o->writeLn("<info>Created:</info> <comment>$file</comment>");
    }
}
