<?php

namespace Stub\Console;

use Stub\Stub;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Create extends Command
{
    public $commandName = 'create';

    protected function configure()
    {
        $this->setName($this->commandName)
            ->setDescription("Turn existing files into stubs")
            ->addArgument('source', InputArgument::REQUIRED, 'What are we stubbing?')
            ->addArgument('output', InputArgument::REQUIRED, 'Where are we outputing?')
            ->addArgument('variables', InputArgument::IS_ARRAY, 'What variables to use?');
    }

    protected function execute(InputInterface $i, OutputInterface $o)
    {
        $source = $i->getArgument('source');
        $output = $i->getArgument('output');
        $variables = $i->getArgument('variables');

        $render = [];

        if (count($variables) == 1 && file_exists($variables[0])) {
            $render = json_decode(file_get_contents($variables[0]), true);
        } else {
            foreach ($variables as $index => $keyValue) {
                $keyValue = explode(':', $keyValue);
                $render[$keyValue[0]] = $keyValue[1];
            }
        }

        $stubs = (new Stub)->source($source)->output($output);

        if ($o->isVerbose()) {
            $stubs->listen(function ($path, $content, $success) use ($o) {
                if ($success) {
                    $o->writeLn('<info>Created</info> <comment>'.$path.'</comment>');
                }
                else {
                    $o->writeLn('<error>Unable to create</error> <comment>'.$path.'</comment>');
                }
            });
        }

        $count = count($stubs->create($render)->rendered);

        if ($count) {
            $o->writeLn('<info>Stub created!</info> <comment>'.$count.'</comment> <info>file(s) created.</info>');
        }
        else {
            $o->writeLn('<error>Unable to create stub! 0 files created.</error>');
        }
    }
}
