<?php
namespace Kwf\Trl;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kwf\Trl\ParseGitBranches;
use Symfony\Component\Console\Input\InputOption;

class ParseWebCommand extends Command
{
    protected function configure()
    {
        $this->setName('parseWeb')
            ->setDescription('Parse your web code for trl function calls')
            ->addOption('branches', null, InputOption::VALUE_OPTIONAL, 'Branches of repository you want to parse');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $branches = $input->getOption('branches') ? explode(',', $input->getOption('branches')) : null;
        $config = parse_ini_file('config.ini');
        $webcodeLanguage = $config['webCodeLanguage'];
        $poFilePath = "trl/$webcodeLanguage.po";
        if (!is_dir('trl')) {
            mkdir('trl');
        }
        $parseScript = new ParseGitBranches(getcwd(), $poFilePath, 'web', $output, false, $branches);
        $parseScript->parse();
    }
}
