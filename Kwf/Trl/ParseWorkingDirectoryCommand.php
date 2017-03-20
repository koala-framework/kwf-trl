<?php
namespace Kwf\Trl;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Kwf\Trl\Parser;

class ParseWorkingDirectoryCommand extends Command
{
    protected function configure()
    {
        $this->setName('parseWD')
            ->setDescription('Parse your working directory code for trl function calls')
            ->addOption('key', 'k', InputOption::VALUE_OPTIONAL+InputOption::VALUE_IS_ARRAY, 'Keys to show source-infos');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $keys = $input->getOption('key');
        $config = parse_ini_file('config.ini');
        $webcodeLanguage = $config['webCodeLanguage'];
        $poFilePath = "trl/$webcodeLanguage.po";
        if (!is_dir('trl')) {
            mkdir('trl');
        }
        $parseScript = new Parser(getcwd(), $poFilePath, 'web', $output, false, $keys);
        $parseScript->parse();
    }
}
