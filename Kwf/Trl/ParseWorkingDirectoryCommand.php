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
            ->addOption('msgId', 'm', InputOption::VALUE_OPTIONAL+InputOption::VALUE_IS_ARRAY, 'msgIds to show source-infos');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $msgIds = $input->getOption('msgId');
        $config = parse_ini_file('config.ini');
        $webcodeLanguage = $config['webCodeLanguage'];
        $poFilePath = "trl/$webcodeLanguage.po";
        if (!is_dir('trl')) {
            mkdir('trl');
        }
        $parseScript = new Parser(getcwd(), $poFilePath, 'web', $output, false, $msgIds);
        $parseScript->parse();
    }
}
