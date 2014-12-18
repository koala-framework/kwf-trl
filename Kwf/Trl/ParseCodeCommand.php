<?php
namespace Kwf\Trl;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kwf\Trl\Parse\ParsePhpForTrl;

class ParseCodeCommand extends Command
{
    protected function configure()
    {
        $this->setName('parseCode')
            ->setDescription('Parse code for trl and trlKwf function calls')
            ->addArgument('path', InputArgument::OPTIONAL, 'Path for po-file', 'trl.po')
            ->addArgument('mask', InputArgument::OPTIONAL, 'Mask to parse for. This can be trl or trlKwf', 'trlKwf')
            ->addArgument('dir', InputArgument::OPTIONAL, 'Path to source directory', '.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $poFilePath = $input->getArgument('path');
        $sourceDir = $input->getArgument('dir');

        // check requirements for js-parser fulfilled
        $output = null;
        $ret = null;
        exec('node -v', $output, $ret);
        if ($ret == 1) {
            echo 'Node needs to be installed';
            echo 'sudo apt-get install nodejs';
            exit(1);
        }

        // call js parser
        echo "Parsing files: js\n";
        $cmd = 'node Kwf/Trl/Parse/ParseJsForTrl.js '.$sourceDir;
        $output = null;
        $ret = null;
        exec($cmd, $output, $ret);
        if ($ret == 1) {
            echo 'Exception with JS parser: '.$output[0];
            exit(1);
        }
        $jsTrls = json_decode($output[0], true);

        // call php parser
        echo "Parsing files: php, tpl\n";
        $trlPhpParser = new ParsePhpForTrl;
        $trlPhpParser->setCodeDirectory($sourceDir);
        $phpTrls = $trlPhpParser->parseCodeDirectory();
        $exceptions = $trlPhpParser->getExceptions();

        $trls = array_merge_recursive($jsTrls, $phpTrls);

        // generate po file
        echo "Generating po file\n";
        $mask = $input->getArgument('mask');
        $poFile = new \Sepia\PoParser;
        $poFile->setInsertOnUpdate(true);
        foreach ($trls as $trlType => $trlsForType) {
            if ($mask == 'trlKwf' && strpos(strtolower($trlType), 'kwf') === false) {
                continue;
            } else if ($mask == 'trl' && strpos(strtolower($trlType), 'kwf') !== false) {
                continue;
            }

            foreach ($trlsForType as $trl) {
                if (strpos($trlType, 'trlcp') !== false) {
                    $poFile->updateEntry($trl['single']);
                    $poFile->setEntryPlural($trl['single'], $trl['plural']);
                    $poFile->setEntryContext($trl['single'], $trl['context']);
                } else if (strpos($trlType, 'trlc') !== false) {
                    $poFile->updateEntry($trl['msg']);
                    $poFile->setEntryContext($trl['msg'], $trl['context']);
                } else if (strpos($trlType, 'trlp') !== false) {
                    $poFile->updateEntry($trl['single']);
                    $poFile->setEntryPlural($trl['single'], $trl['plural']);
                } else if (strpos($trlType, 'trl') !== false) {
                    $poFile->updateEntry($trl);
                }
            }
        }
        $poFile->write($poFilePath);
        exit();
    }
}
