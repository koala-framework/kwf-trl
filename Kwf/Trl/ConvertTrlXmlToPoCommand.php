<?php
namespace Kwf\Trl;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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
            ->addArgument('poPath', InputArgument::OPTIONAL, 'Filename for trl po file', 'trl.po')
            ->addArgument('baseLanguage', InputArgument::OPTIONAL, 'Language used as id (default is en)', 'en')
            ->addArgument('targetLanguage', InputArgument::OPTIONAL, 'Language used for translation (default is none)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $xmlPath = $input->getArgument('xmlPath');
        $poPath = $input->getArgument('poPath');
        $baseLanguage = $input->getArgument('baseLanguage');
        $targetLanguage = $input->getArgument('targetLanguage');

        $trlXmlToPoConverter = new TrlXmlToPoConverter();
        $trlXmlToPoConverter->setXmlPath($xmlPath);
        $trlXmlToPoConverter->setBaseLanguage($baseLanguage);
        if ($targetLanguage) $trlXmlToPoConverter->setTargetLanguage($targetLanguage);
        $trlXmlToPoConverter->convertToPo();

        $trlXmlToPoConverter->writePoContent($poPath);
        exit();
    }
}
