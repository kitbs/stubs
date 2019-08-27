<?php

namespace Stub\Console;

use Stub\Stub;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Init extends Command
{
    public $commandName = 'init';

    protected function configure()
    {
        $this->setName($this->commandName);
        $this->setDescription("Create stub.json with values interactively");
    }

    protected function execute(InputInterface $i, OutputInterface $o)
    {
        $io = new SymfonyStyle($i, $o);
        $io->title('Initializing Stub Values');
        $io->text('Generate a stub.json file: {"search": "replace"}');

        $io->newLine();

        $values = [];
        $continue = true;
        $helper = $this->getHelper('question');

        while ($continue) {
            $question = new Question('Search: ');
            $search = $helper->ask($i, $o, $question);
            $question = new Question('Replace: ');
            $replace = $helper->ask($i, $o, $question);

            $values[$search] = $replace;

            $question = new Question('Continue? (y,n)', 'y');
            $continue = $helper->ask($i, $o, $question);
            $continue = ['y' => true, 'n' => false][strtolower($continue)];

            $io->newLine();
        }

        file_put_contents('stub.json', json_encode($values, JSON_PRETTY_PRINT));

        $o->writeLn('<info>Created:</info> <comment>stub.json</comment>');
    }
}
