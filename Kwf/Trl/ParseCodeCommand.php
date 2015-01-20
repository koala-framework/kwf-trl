<?php
namespace Kwf\Trl;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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
            ->addArgument('path', InputArgument::OPTIONAL, 'Path for po-file', 'trl.po')
            ->addArgument('mask', InputArgument::OPTIONAL, 'Mask to parse for. This can be trl or trlKwf', 'trlKwf')
            ->addArgument('dir', InputArgument::OPTIONAL, 'Path to source directory', '.')
            ->addArgument('kwf', InputArgument::OPTIONAL, 'Path to kwf directory (only if parsing package)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $poFilePath = $input->getArgument('path');
        $sourceDir = $input->getArgument('dir');
        $kwfDir = $input->getArgument('kwf');

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

        $kwfTrlElements = array(
            'trlcp' => array(),
            'trlc' => array(),
            'trlp' => array(),
            'trl' => array()
        );
        if ($kwfDir) {
            $output->writeln('Parsing kwf directory...');
            $kwfTrlElements = $this->_parseDirectoryForTrl($kwfDir, $output);
        }

        // generate po file
        $output->writeln('Generating po file');
        $mask = $input->getArgument('mask');
        $poFile = new \Sepia\PoParser;
        foreach ($trlElements as $trlElement) {
            if ($mask == 'trlKwf' && strpos(strtolower($trlType), 'kwf') === false) {
                continue;
            } else if ($mask == 'trl' && strpos(strtolower($trlType), 'kwf') !== false) {
                continue;
            }

            foreach ($trlsForType as $trl) {
                // Check if translation is in kwf
                $trlFoundInKwf = false;
                foreach ($kwfTrls as $kwfTrlsOfType) {
                    if (in_array($trl, $kwfTrlsOfType)) {
                        $trlFoundInKwf = true;
                        break;
                    }
                }
                if ($trlFoundInKwf) continue;

                if (strpos($trlType, 'trlcp') !== false) {
                    $poFile->updateEntry($trl['single'], $trl['single'], array(), array(), array(), true);
                    $poFile->setEntryPlural($trl['single'], $trl['plural']);
                    $poFile->setEntryContext($trl['single'], $trl['context']);
                } else if (strpos($trlType, 'trlc') !== false) {
                    $poFile->updateEntry($trl['msg'], $trl['msg'], array(), array(), array(), true);
                    $poFile->setEntryContext($trl['msg'], $trl['context']);
                } else if (strpos($trlType, 'trlp') !== false) {
                    $poFile->updateEntry($trl['single'], $trl['single'], array(), array(), array(), true);
                    $poFile->setEntryPlural($trl['single'], $trl['plural']);
                } else if (strpos($trlType, 'trl') !== false) {
                    $poFile->updateEntry($trl, $trl, array(), array(), array(), true);
                }
            }
        }
        $poFile->writeFile($poFilePath);
    }

    private function _parseDirectoryForTrl($sourceDir, $output)
    {
        // call js parser
        $output->writeln('Parsing files: js');
        $trlJsParser = new ParseJsForTrl($sourceDir);
        $jsTrls = $trlJsParser->parse();
        var_dump($jsTrls);

        // call php parser
        $output->writeln('Parsing files: php, tpl');
        $trlPhpParser = new ParsePhpForTrl;
        $trlPhpParser->setCodeDirectory($sourceDir);
        $phpTrls = $trlPhpParser->parseCodeDirectory();
        $exceptions = $trlPhpParser->getExceptions();

        return array_merge_recursive($jsTrls, $phpTrls);
    }
}
