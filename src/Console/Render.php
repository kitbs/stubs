<?php

namespace Stub\Console;

use Stub\Stub;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Render extends Command
{
    public $commandName = 'render';

    protected function configure()
    {
        $this->setName($this->commandName)
            ->setDescription("Take a source, replace variables & output it")
            ->addArgument('source', InputArgument::REQUIRED, 'What are we stubbing?')
            ->addArgument('output', InputArgument::REQUIRED, 'Where are we outputing?')
            ->addArgument('variables', InputArgument::IS_ARRAY, 'What variables to use?')
            ->addOption('varset', null, InputOption::VALUE_OPTIONAL, 'Class name of a variable set to use.');
    }

    protected function execute(InputInterface $i, OutputInterface $o)
    {
        $source = $i->getArgument('source');
        $output = $i->getArgument('output');
        $variables = $i->getArgument('variables');
        $varset = $i->getOption('varset');

        $render = [];
        $base = null;

        if (count($variables) == 1 && file_exists($variables[0])) {
            $render = json_decode(file_get_contents($variables[0]), true);
        } else {
            foreach ($variables as $index => $variable) {
                $keyValue = explode(':', $variable, 2);
                if (isset($keyValue[1])) {
                    $render[$keyValue[0]] = $keyValue[1];
                }
                elseif ($index == 0 && $varset) {
                    $base = $keyValue[0];
                }
                else {
                    return $o->writeLn('<error>Invalid variable format `'.$variable.'`</error>');
                }
            }
        }

        if ($varset) {
            if (!$base) {
                return $o->writeLn('<error>No base variable defined for variable set</error>');
            }
            $render = new $varset($base, $render);
        }

        $stubs = (new Stub)->source($source)->output($output);

        if ($o->isVerbose()) {
            $stubs->listen(function ($path, $content, $success) use ($o) {
                if ($success) {
                    $o->writeLn('<info>Rendered</info> <comment>'.$path.'</comment>');
                }
                else {
                    $o->writeLn('<error>Unable to render</error> <comment>'.$path.'</comment>');
                }
            });
        }

        $count = $stubs->render($render);

        if ($count) {
            $o->writeLn('<info>Stub rendered!</info> <comment>'.$count.'</comment> <info>file(s) rendered.</info>');
        }
        else {
            $o->writeLn('<error>Unable to render stub! 0 files rendered.</error>');
        }
    }
}
