<?php

namespace Stub\Console;

use Stub\Stub;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Parse extends Command
{
    protected function configure()
    {
        $this->setName("parse")
            ->setDescription("Take a source, replace variables & output it")
            ->addArgument('source', InputArgument::REQUIRED, 'What are we stubbing?')
            ->addArgument('output', InputArgument::REQUIRED, 'Where are we outputing?')
            ->addArgument('variables', InputArgument::IS_ARRAY, 'What variables to use?');
    }

    protected function execute(InputInterface $i, OutputInterface $o)
    {
        $source = $i->getArgument('source');
        $output = $i->getArgument('output');
        $variables = $i->getArgument('variables');

        $parse = [];

        if (count($variables) == 1 && file_exists($variables[0])) {
            $parse = json_decode(file_get_contents($variables[0]), true);
        } else {
            foreach ($variables as $index => $keyValue) {
                $keyValue = explode(':', $keyValue);
                $parse[$keyValue[0]] = $keyValue[1];
            }
        }

        (new Stub)->source($source)->output($output)->parse($parse);

        $o->writeLn('<info>Stub success!</info>');
    }
}
