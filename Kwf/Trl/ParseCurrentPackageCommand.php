<?php
namespace Kwf\Trl;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Kwf\Trl\ParseGitBranches;

class ParseCurrentPackageCommand extends Command
{
    protected function configure()
    {
        $this->setName('parseCurrentPackage')
            ->setDescription('Parse your current package code for trlKwf function calls')
            ->addOption('branches', null, InputOption::VALUE_OPTIONAL, 'Branches of repository you want to parse');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $branches = $input->getOption('branches') ? explode(',', $input->getOption('branches')) : null;
        $poFilePath = "trl/en.po";
        if (!is_dir('trl')) {
            mkdir('trl');
        }
        $parseScript = new ParseGitBranches(getcwd(), $poFilePath, 'kwf', $output, true, $branches);
        $parseScript->parse();
    }
}
