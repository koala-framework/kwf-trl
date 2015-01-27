<?php
namespace Kwf\Trl;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kwf\Trl\Parse\ParsePhpForTrl;
use Kwf\Trl\Parse\ParseJsForTrl;
use Kwf\Trl\Parse\ParseAll;
use Kwf\Trl\Utils\PoFileGenerator;
use Kwf\Trl\Utils\TrlElementsExtractor;

class ParseCodeCommand extends Command
{
    protected function configure()
    {
        $this->setName('parseCode')
            ->setDescription('Parse code for trl and trlKwf function calls')
            ->addArgument('dir', InputArgument::OPTIONAL, 'Path to source directory', null)
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Path for po-file', 'trl.po')
            ->addOption('mask', 'm', InputOption::VALUE_REQUIRED, 'Mask to parse for. This can be trl or trlKwf', 'trlKwf')
            ->addOption('kwfpath', 'k', InputOption::VALUE_REQUIRED, 'Path to kwf po-file (only if parsing package)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourceDir = $input->getArgument('dir');
        $poFilePath = $input->getOption('path');
        $kwfPoFilePath = $input->getOption('kwfpath');

        // parse package
        $output->writeln('Parsing source directory...');
        $parser = new ParseAll($sourceDir);
        $trlElements = $parser->parseDirectoryForTrl();

        $kwfTrlElements = array();
        if ($kwfPoFilePath) {
            $output->writeln('Parsing kwf directory...');
            $kwfPoFile = new \Sepia\PoParser;
            $kwfPoFile->parseFile($kwfPoFilePath);
            $trlElementsExtractor = new TrlElementsExtractor($kwfPoFile);
            $kwfTrlElements = $trlElementsExtractor->extractTrlElements();
        }

        // generate po file
        $output->writeln('Generate Po-File...');
        $poFileGenerator = new PoFileGenerator($trlElements, $kwfTrlElements);
        $poFile = $poFileGenerator->generatePoFileObject();
        $poFile->writeFile($poFilePath);

        if (count($parser->getErrors())) {
            $output->writeln('Trl Errors:');
            foreach ($parser->getErrors() as $error) {
                var_dump($error);
            }
        }
    }
}
