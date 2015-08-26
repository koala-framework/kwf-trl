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
            ->setDescription('Parse koala-framework code for trlKwf function calls')
            ->addArgument('kwf-path', InputArgument::REQUIRED, 'Path to kwf-folder you want to parse');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packagePath = $input->getArgument('kwf-path');
        $trlFolder = "$packagePath/trl";
        if (!is_dir($trlFolder)) {
            mkdir($trlFolder);
        }
        $parseScript = new Parser($packagePath, "$trlFolder/en.po", 'trlKwf', $output);
        $parseScript->setIgnoredFiles(array(
            "$packagePath/Kwf/Trl.php",
            "$packagePath/Kwf/Component/Data.php",
            "$packagePath/Kwf/Controller/Action/Debug/ApcController.php"
        ));
        $parseScript->parse();
    }
}
