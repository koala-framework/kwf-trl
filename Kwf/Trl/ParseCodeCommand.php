<?php
namespace Kwf\Trl;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kwf\Trl\Parse\ParsePhpForTrl;
use Kwf\Trl\Parse\ParseJsForTrl;

class ParseCodeCommand extends Command
{
    protected function configure()
    {
        $this->setName('parseCode')
            ->setDescription('Parse code for trl and trlKwf function calls')
            ->addArgument('dir', InputArgument::OPTIONAL, 'Path to source directory', null)
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Path for po-file', 'trl.po')
            ->addOption('mask', 'm', InputOption::VALUE_REQUIRED, 'Mask to parse for. This can be trl or trlKwf', 'trlKwf')
            ->addOption('kwf', null, InputOption::VALUE_REQUIRED, 'Path to kwf directory (only if parsing package)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourceDir = $input->getArgument('dir');
        $poFilePath = $input->getOption('path');
        $kwfDir = $input->getOption('kwf');

        // check requirements for js-parser fulfilled
        $ret = null;
        exec('node -v', $cmdOutput, $ret);
        if ($ret == 1) {
            $output->writeln('<error>Node needs to be installed</error>');
            $output->writeln('<error>sudo apt-get install nodejs</error>');
            exit(1);
        }

        // parse package
        $output->writeln('Parsing source directory...');
        $trlElements = $this->_parseDirectoryForTrl($sourceDir, $output);

        $kwfTrlElements = array();
        if ($kwfDir) {
            $output->writeln('Parsing kwf directory...');
            $kwfTrlElements = $this->_parseDirectoryForTrl($kwfDir, $output);
        }

        // generate po file
        $output->writeln('');
        $output->writeln('Generating po file');
        $mask = $input->getOption('mask');
        $poFile = new \Sepia\PoParser;
        $errors = array();
        foreach ($trlElements as $trlElement) {
            // Check if translation is in kwf
            $trlFoundInKwf = false;
            foreach ($kwfTrlElements as $kwfTrlElement) {
                if ($kwfTrlElement['type'] == $trlElement['type']
                    && $kwfTrlElement['text'] == $trlElement['text']
                ) {
                    $trlFoundInKwf = true;
                    break;
                }
            }
            if ($trlFoundInKwf) continue;
            if (isset($trlElement['error_short'])) {
                $errors[] = $trlElement;
                continue;
            }

            if ($trlElement['type'] == 'trlcp') {
                $poFile->updateEntry($trlElement['text'], $trlElement['text'], array(), array(), array(), true);
                $poFile->updateEntryPlural($trlElement['text'], $trlElement['plural']);
                $poFile->updateEntryContext($trlElement['text'], $trlElement['context']);
            } else if ($trlElement['type'] ==  'trlc') {
                $poFile->updateEntry($trlElement['text'], $trlElement['text'], array(), array(), array(), true);
                $poFile->updateEntryContext($trlElement['text'], $trlElement['context']);
            } else if ($trlElement['type'] == 'trlp') {
                $poFile->updateEntry($trlElement['text'], $trlElement['text'], array(), array(), array(), true);
                $poFile->updateEntryPlural($trlElement['text'], $trlElement['plural']);
            } else if ($trlElement['type'] == 'trl') {
                $poFile->updateEntry($trlElement['text'], $trlElement['text'], array(), array(), array(), true);
            }
        }
        $output->writeln('Write Po File');
        $poFile->writeFile($poFilePath);

        if (count($errors)) {
            $output->writeln('Trl Errors:');
            foreach ($errors as $error) {
                var_dump($error);
            }
        }
    }

    private function _parseDirectoryForTrl($sourceDir, $output)
    {
        // call js parser
        $output->writeln('Parsing files: js');
        $trlJsParser = new ParseJsForTrl($sourceDir);
        $jsTrls = $trlJsParser->parse();

        // call php parser
        $output->writeln('Parsing files: php, tpl');
        $trlPhpParser = new ParsePhpForTrl;
        $trlPhpParser->setCodeDirectory($sourceDir);
        $phpTrls = $trlPhpParser->parseCodeDirectory();
        $output->writeln('');
        $output->writeln('File Errors');
        foreach ($trlPhpParser->getErrors() as $error) {
            $output->writeln($error['file']);
            $output->writeln($error['error']->getRawMessage());
        }
        return array_merge_recursive($jsTrls, $phpTrls);
    }
}
