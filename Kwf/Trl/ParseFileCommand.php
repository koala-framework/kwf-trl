<?php
namespace Kwf\Trl;

use Kwf\Trl\Parse\ParseJsForTrl;
use Kwf\Trl\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\SplFileInfo;

class ParseFileCommand extends Command
{
    protected function configure()
    {
        $this->setName('parseFile')
            ->setDescription('Parse a single file for trl function calls, mainly to debug parsing.')
            ->addArgument('path', InputArgument::REQUIRED, 'File to parse')
            ->addOption('msgId', 'm', InputOption::VALUE_OPTIONAL+InputOption::VALUE_IS_ARRAY, 'msgIds to show source-infos');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $file = new \SplFileInfo($path);
        if ($file->getExtension() == 'js') {
            $isJsx = false;
            var_dump(\Kwf_TrlJsParser_JsParser::parseContent(file_get_contents($path), $isJsx));
        } else if ($file->getExtension() == 'jsx') {
            $isJsx = true;
            var_dump(\Kwf_TrlJsParser_JsParser::parseContent(file_get_contents($path), $isJsx));
        } else {
            throw new \Exception('Filetype not supported');
        }
    }
}
