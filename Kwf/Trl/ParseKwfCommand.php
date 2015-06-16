<?php
namespace Kwf\Trl;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Kwf\Trl\Parse\Script;

class ParseKwfCommand extends Command
{
    protected function configure()
    {
        $this->setName('parseKwf')
            ->setDescription('Parse koala-framework code for trlKwf function calls');
//             ->addArgument('package-name', InputArgument::REQUIRED, 'Package-name of repository you want to parse against koala-framework');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packagePath = VENDOR_PATH."/koala-framework/koala-framework";
        $trlFolder = "$packagePath/trl";
        if (!is_dir($trlFolder)) {
            mkdir($trlFolder);
        }
        $parseScript = new Script($packagePath, "$trlFolder/en.po", 'trlKwf', $output);
        $parseScript->parse();
    }
}
