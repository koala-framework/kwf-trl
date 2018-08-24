<?php
namespace Kwf\Trl;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Kwf\Trl\ParseGitBranches;

class ParseWebCommand extends Command
{
    protected function configure()
    {
        $this->setName('parseWeb')
            ->setDescription('Parse your web code for trl function calls')
            ->addOption('ignore-branches', 'ib', InputOption::VALUE_OPTIONAL, 'List of branches to ignore while parsing.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = parse_ini_file('config.ini');
        $webcodeLanguage = $config['webCodeLanguage'];
        $poFilePath = "trl/$webcodeLanguage.po";
        if (!is_dir('trl')) {
            mkdir('trl');
        }
        $parseScript = new ParseGitBranches(getcwd(), $poFilePath, 'web', $output);
        if ($ignoreBranches = $input->getOption('ignore-branches')) {
            $parseScript->setIgnoredBranches(explode(",", $ignoreBranches));
        }
        $parseScript->parse();
    }
}
