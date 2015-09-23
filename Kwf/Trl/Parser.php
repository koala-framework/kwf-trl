<?php
namespace Kwf\Trl;

use Gitonomy\Git\Repository;
use Kwf\Trl\Parse\ParseAll;
use Kwf\Trl\Utils\PoFileGenerator;
use Kwf\Trl\Utils\TrlElementsExtractor;

class Parser
{
    protected $_kwfPoFilePath;
    protected $_directory;
    protected $_poFilePath;
    protected $_mask;

    protected $_ignoredFiles = array();

    protected $_output;

    function __construct($directory, $poFilePath, $mask, $output, $kwfPoFilePath = false)
    {
        $this->_kwfPoFilePath = $kwfPoFilePath;
        $this->_directory = $directory;
        $this->_poFilePath = $poFilePath;
        $this->_mask = $mask;
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

        $initDir = getcwd();
        $this->_output->writeln('<info>Changing into directory</info>');
        $repository = new Repository($this->_directory);
        $wc = $repository->getWorkingCopy();

        if ($wc->getDiffPending()->getRawDiff() != ''
            || $wc->getDiffStaged()->getRawDiff() != ''
        ) {
            $this->_output->writeln('<error>Uncommited changes</error>');
            exit;
        }

        chdir($initDir);
        $initBranch = trim($repository->run('rev-parse', array('--abbrev-ref', 'HEAD')));

        $trlElements = array();
        $errors = array();
        $repository = new Repository($this->_directory);
        $repository->run('fetch');
        if ($this->_kwfPoFilePath) {
            $this->_output->writeln('<info>Iterating over branches matching "^[1-9]+.[0-9]+$"</info>');
        } else {
            $this->_output->writeln('<info>Iterating over branches matching "^[3-9]+.[0-9]+$" and >=3.9</info>');
        }
        foreach ($repository->getReferences()->getBranches() as $branch) {
            $branchName = $branch->getName();
            if (strpos($branchName, 'origin/') === false) continue;
            $splited = explode('/', $branchName);
            $isVersionNumber = preg_match('/^[0-9]+.[0-9]+$/i', $splited[1]);
            if (sizeof($splited) >= 3) continue;
            if (!$isVersionNumber && $splited[1] != 'master' && $splited[1] != 'production') continue;

            if (!$this->_kwfPoFilePath && $isVersionNumber && version_compare($splited[1], '3.9', '<')) continue;

            $this->_output->writeln("<info>Checking out branch: $branchName</info>");
            $wc->checkout($branchName);
            // parse package
            $this->_output->writeln('Parsing source directory...');
            $parser = new ParseAll($this->_directory, $this->_output);
            $parser->setIgnoredFiles($this->_ignoredFiles);
            $trlElements = array_merge($parser->parseDirectoryForTrl(), $trlElements);
            $newErrors = $parser->getErrors();
            foreach ($newErrors as $key => $error) {
                $newErrors[$key]['branch'] = $branchName;
            }
            $errors = array_merge($newErrors, $errors);
        }
        $wc->checkout($initBranch);

        // generate po file
        $this->_output->writeln('Generate Po-File...');
        touch($this->_poFilePath);
        $this->_output->writeln($this->_poFilePath);

        $poFileGenerator = new PoFileGenerator($trlElements, $kwfTrlElements);
        $poFile = $poFileGenerator->generatePoFileObject($this->_poFilePath);
        $poFile->writeFile($this->_poFilePath);

        $this->_output->writeln('');
        $this->_output->writeln('Trl Errors:');
        foreach ($trlElements as $trlElement) {
            if (isset($trlElement['error_short']) && $trlElement['error_short']) {
                var_dump($trlElement);
            }
        }

        if (count($errors)) {
            $this->_output->writeln('');
            $this->_output->writeln('Php Parse-Errors:');
            foreach ($errors as $error) {
                $this->_output->writeln('  Branch: '.$error['branch'].' | File: '.$error['file']);
                $this->_output->writeln('    Error:'.$error['error']->getMessage());
                $this->_output->writeln('  -------------------------------------');
            }
        }
    }
}
