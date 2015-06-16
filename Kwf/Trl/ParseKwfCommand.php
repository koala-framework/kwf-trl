<?php
namespace Kwf\Trl;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Kwf\Trl\Parser;

class ParseKwfCommand extends Command
{
    protected function configure()
    {
        $this->setName('parseKwf')
            ->setDescription('Parse koala-framework code for trlKwf function calls');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packagePath = 'vendor/koala-framework/koala-framework';
        $trlFolder = "$packagePath/trl";
        if (!is_dir($trlFolder)) {
            mkdir($trlFolder);
        }
        $parseScript = new Parser($packagePath, "$trlFolder/en.po", 'trlKwf', $output);
        $parseScript->parse();
    }
}
