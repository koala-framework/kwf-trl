<?php
namespace Kwf\Trl;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kwf\Trl\Parser;

class ParseWorkingDirectoryCommand extends Command
{
    protected function configure()
    {
        $this->setName('parseWD')
            ->setDescription('Parse your working directory code for trl function calls');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = parse_ini_file('config.ini');
        $webcodeLanguage = $config['webCodeLanguage'];
        $poFilePath = "trl/$webcodeLanguage.po";
        if (!is_dir('trl')) {
            mkdir('trl');
        }
        $parseScript = new Parser(getcwd(), $poFilePath, 'web', $output);
        $parseScript->parse();
    }
}
