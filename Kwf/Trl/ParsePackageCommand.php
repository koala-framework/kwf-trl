<?php
namespace Kwf\Trl;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Kwf\Trl\ParseGitBranches;

class ParsePackageCommand extends Command
{
    protected function configure()
    {
        $this->setName('parsePackage')
            ->setDescription('Parse your package code for trlKwf function calls')
            ->addArgument('package-name', InputArgument::REQUIRED, 'Package-name of repository you want to parse against koala-framework');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packageName = $input->getArgument('package-name');
        $packagePath = "vendor/$packageName";
        $trlFolder = "$packagePath/trl";
        if (!is_dir($trlFolder)) {
            mkdir($trlFolder);
        }
        $parseScript = new ParseGitBranches($packagePath, "$trlFolder/en.po", 'kwf', $output, true);
        $parseScript->parse();
    }
}
