<?php
namespace Kwf\Trl;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kwf\Trl\Parse\Script;

class ParseWebCommand extends Command
{
    protected function configure()
    {
        $this->setName('parseWeb')
            ->setDescription('Parse your web code for trl function calls');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = parse_ini_file('config.ini');
        $webcodeLanguage = $config['webCodeLanguage'];
        $poFilePath = "trl/$webcodeLanguage.po";
        if (!is_dir('trl')) {
            mkdir('trl');
        }
        $parseScript = new Script(getcwd(), $poFilePath, 'trl', $output);
        $parseScript->parse();
    }
}
