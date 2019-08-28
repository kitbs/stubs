<?php

namespace Stub\Console;

use Stub\Stub;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
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
            ->addArgument('variables', InputArgument::IS_ARRAY, 'What variables to use?');
    }

    protected function execute(InputInterface $i, OutputInterface $o)
    {
        $source = $i->getArgument('source');
        $output = $i->getArgument('output');
        $variables = $i->getArgument('variables');

        $render = [];


        if (count($variables) == 0) {
            $helper = $this->getHelper('question');
            $settings = (new Stub)->settings($source);
            foreach ($settings as $question => $var) {
                $question = new Question("$question: ");
                $render[$var] = $helper->ask($i, $o, $question);
            }
        }

        if (count($variables) == 1 && file_exists($variables[0])) {
            $render = json_decode(file_get_contents($variables[0]), true);
        }

        if (empty($render)) {
            foreach ($variables as $index => $keyValue) {
                $keyValue = explode(':', $keyValue);
                $render[$keyValue[0]] = $keyValue[1];
            }
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
