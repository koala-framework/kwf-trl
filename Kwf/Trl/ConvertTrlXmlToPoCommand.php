<?php
namespace Kwf\Trl;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kwf\Trl\Convert\TrlXmlToPoConverter;

class ConvertTrlXmlToPoCommand extends Command
{
    protected function configure()
    {
        $this->setName('convertTrlXmlToPo')
            ->setDescription('Convert xml-trl file into po-trl file')
            ->addArgument('xmlPath', InputArgument::REQUIRED, 'Path to trl xml file')
            ->addOption('poPath', 'p', InputOption::VALUE_OPTIONAL, 'Filename for trl po file', 'trl.po')
            ->addOption('baseLanguage', 'b', InputOption::VALUE_OPTIONAL, 'Language used as id (default is en)', 'en')
            ->addOption('targetLanguage', 't', InputOption::VALUE_OPTIONAL, 'Language used for translation (default is none)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $xmlPath = $input->getArgument('xmlPath');
        $poPath = $input->getOption('poPath');
        $baseLanguage = $input->getOption('baseLanguage');
        $targetLanguage = $input->getOption('targetLanguage');

        $trlXmlToPoConverter = new TrlXmlToPoConverter();
        $trlXmlToPoConverter->setXmlPath($xmlPath);
        $trlXmlToPoConverter->setBaseLanguage($baseLanguage);
        if ($targetLanguage) $trlXmlToPoConverter->setTargetLanguage($targetLanguage);
        $trlXmlToPoConverter->convertToPo($output);

        $trlXmlToPoConverter->writePoContent($poPath);
    }
}
