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
        $cmdOutput = null;
        $ret = null;
        exec('node -v', $cmdOutput, $ret);
        if ($ret == 1) {
            $output->writeln('<error>Node needs to be installed</error>');
            $output->writeln('<error>sudo apt-get install nodejs</error>');
            exit(1);
        }

        // call js parser
        $output->writeln('Parsing files: js');
        $cmd = 'node Kwf/Trl/Parse/ParseJsForTrl.js '.$sourceDir;
        $cmdOutput = null;
        $ret = null;
        exec($cmd, $cmdOutput, $ret);
        if ($ret == 1) {
            $output->writeln('<error>Exception with JS parser: '.$cmdOutput[0].'</error>');
            exit(1);
        }
        $jsTrls = json_decode($cmdOutput[0], true);

        // call php parser
        $output->writeln('Parsing files: php, tpl');
        $trlPhpParser = new ParsePhpForTrl;
        $trlPhpParser->setCodeDirectory($sourceDir);
        $phpTrls = $trlPhpParser->parseCodeDirectory();
        $exceptions = $trlPhpParser->getExceptions();

        $trls = array_merge_recursive($jsTrls, $phpTrls);

        // generate po file
        $output->writeln('Generating po file');
        $mask = $input->getArgument('mask');
        $poFile = new \Sepia\PoParser;
        foreach ($trls as $trlType => $trlsForType) {
            if ($mask == 'trlKwf' && strpos(strtolower($trlType), 'kwf') === false) {
                continue;
            } else if ($mask == 'trl' && strpos(strtolower($trlType), 'kwf') !== false) {
                continue;
            }

            foreach ($trlsForType as $trl) {
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
        exit();
    }
}
