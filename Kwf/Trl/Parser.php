<?php
namespace Kwf\Trl;

use Gitonomy\Git\Repository;
use Kwf\Trl\Parse\ParseAll;
use Kwf\Trl\Utils\PoFileGenerator;
use Kwf\Trl\Utils\TrlElementsExtractor;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class Parser
{
    protected $_kwfPoFilePath;
    protected $_directory;
    protected $_poFilePath;
    protected $_source;

    protected $_infoForMsgId;

    protected $_ignoredFiles = array();

    protected $_output;

    function __construct($directory, $poFilePath, $source, OutputInterface $output, $kwfPoFilePath = false, $infoForMsgId = array())
    {
        $this->_kwfPoFilePath = $kwfPoFilePath;
        $this->_directory = $directory;
        $this->_poFilePath = $poFilePath;
        $this->_source = $source;
        $this->_infoForMsgId = $infoForMsgId;
        $this->_output = $output;
    }

    public function setIgnoredFiles($paths)
    {
        $this->_ignoredFiles = $paths;
    }

    public function parse()
    {
        $kwfTrlElements = array();
        if ($this->_kwfPoFilePath) {
            $this->_output->writeln('<info>Reading kwf-po file</info>');
            $kwfPoFile = \Sepia\PoParser::parseFile($this->_kwfPoFilePath);
            $trlElementsExtractor = new TrlElementsExtractor($kwfPoFile);
            $kwfTrlElements = $trlElementsExtractor->extractTrlElements();
        }



        // PARSE
        $this->_output->writeln('Parsing source directory...');
        $this->_output->writeln('');
        $parser = new ParseAll($this->_directory, $this->_output);
        $parser->setIgnoredFiles($this->_ignoredFiles);
        $trlElements = $parser->parseDirectoryForTrl();
        $errors = $parser->getErrors();



        // OUTPUT RESULTS
        if (count($this->_infoForMsgId)) {
            $this->_output->writeln('');
            $this->_output->writeln('---------------------------------------------------------');
            $this->_output->writeln('');
            $this->_output->writeln('Output source-info for key:');
            $this->_output->writeln('');
        }
        $filteredTrlElements = array();
        foreach ($trlElements as $trlElement) {
            if (isset($trlElement['text']) && in_array($trlElement['text'], $this->_infoForMsgId)) {
                var_dump($trlElement);
            }
            if ($trlElement['source'] == $this->_source) {
                $filteredTrlElements[] = $trlElement;
            }
        }
        $trlElements = $filteredTrlElements;


        $this->_output->writeln('');
        $this->_output->writeln('---------------------------------------------------------');
        $this->_output->writeln('');
        $this->_output->writeln('Generate Po-File...');
        touch($this->_poFilePath);
        $this->_output->writeln($this->_poFilePath);

        $poFileGenerator = new PoFileGenerator($trlElements, $kwfTrlElements);
        $poFile = $poFileGenerator->generatePoFileObject($this->_poFilePath);
        $poFile->writeFile($this->_poFilePath);


        $this->_output->writeln('');
        $this->_output->writeln('---------------------------------------------------------');
        $this->_output->writeln('');
        $this->_output->writeln('Trl Errors:');
        $this->_output->writeln('');
        foreach ($trlElements as $trlElement) {
            if (isset($trlElement['error_short']) && $trlElement['error_short']) {
                var_dump($trlElement);
            }
        }

        if (count($errors)) {
            $this->_output->writeln('');
            $this->_output->writeln('Php Parse-Errors:');
            foreach ($errors as $error) {
                $this->_output->writeln('  File: '.$error['file']);
                $this->_output->writeln('    Error:'.$error['error']->getMessage());
                $this->_output->writeln('  -------------------------------------');
            }
        }
    }
}
