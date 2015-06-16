<?php
namespace Kwf\Trl\Parse;

use Kwf\Trl\Parse\ParsePhpForTrl;
use Kwf\Trl\Parse\ParseJsForTrl;
use Kwf\Trl\Parse\ParseAll;
use Kwf\Trl\Utils\PoFileGenerator;
use Kwf\Trl\Utils\TrlElementsExtractor;
use Symfony\Component\Console\Output\OutputInterface;

class Script
{
    protected $_kwfPoFilePath;
    protected $_directory;
    protected $_poFilePath;
    protected $_mask;

    protected $_output;

    function __construct($directory, $poFilePath, $mask, $output, $kwfPoFilePath = false)
    {
        $this->_kwfPoFilePath = $kwfPoFilePath;
        $this->_directory = $directory;
        $this->_poFilePath = $poFilePath;
        $this->_mask = $mask;
        $this->_output = $output;
    }

    public function parse()
    {
        $kwfTrlElements = array();
        if ($this->_kwfPoFilePath) {
            $this->_output->writeln('<info>Reading kwf-po file</info>');
            $kwfPoFile = new \Sepia\PoParser;
            $kwfPoFile->parseFile($this->_kwfPoFilePath);
            $trlElementsExtractor = new TrlElementsExtractor($kwfPoFile);
            $kwfTrlElements = $trlElementsExtractor->extractTrlElements();
        }

        $initDir = getcwd();
        $this->_output->writeln('<info>Changing into directory</info>');
        chdir($this->_directory);
        exec('git diff', $diff, $ret);
        exec('git diff --cached', $diffCached, $ret);
        if ($diff || $diffCached) {
            $this->_output->writeln('<error>Uncommited changes</error>');
            exit;
        }
        chdir($initDir);

        $trlElements = array();
        $errors = array();
        $git = new \Kwf_Util_Git($this->_directory);
        $git->fetch();
        $initBranch = $git->getActiveBranch();
        if ($this->_kwfPoFilePath) {
            $this->_output->writeln('<info>Iterating over branches matching "^[1-9]+.[0-9]+$"</info>');
        } else {
            $this->_output->writeln('<info>Iterating over branches matching "^[3-9]+.[0-9]+$" and >=3.9</info>');
        }
        foreach ($git->getBranches('-r') as $branch) {
            if (strpos($branch, 'origin/') === false) continue;
            $splited = explode('/', $branch);
            $isVersionNumber = preg_match('/^[0-9]+.[0-9]+$/i', $splited[1]);
            if (sizeof($splited) >= 3) continue;
            if (!$isVersionNumber && $splited[1] != 'master' && $splited[1] != 'production') continue;

            if (!$this->_kwfPoFilePath && $isVersionNumber && version_compare($splited[1], '3.9', '<')) continue;

            $this->_output->writeln("<info>Checking out branch: $branch</info>");
            $git->checkout($branch);
            // parse package
            $this->_output->writeln('Parsing source directory...');
            $parser = new ParseAll($this->_directory, $this->_output);
            $trlElements = array_merge($parser->parseDirectoryForTrl(), $trlElements);
            $newErrors = $parser->getErrors();
            foreach ($newErrors as $key => $error) {
                $newErrors[$key]['branch'] = $branch;
            }
            $errors = array_merge($newErrors, $errors);
        }
        $git->checkout($initBranch);

        // generate po file
        $this->_output->writeln('Generate Po-File...');
        $poFileGenerator = new PoFileGenerator($trlElements, $kwfTrlElements);
        $poFile = $poFileGenerator->generatePoFileObject();
        $poFile->writeFile($this->_poFilePath);

        $this->_output->writeln('Trl Errors:');
        foreach ($trlElements as $trlElement) {
            if (isset($trlElement['error_short']) && $trlElement['error_short']) {
                var_dump($trlElement);
            }
        }

        if (count($errors)) {
            $this->_output->writeln('Php Parse-Errors:');
            foreach ($errors as $error) {
                var_dump($error);
            }
        }
    }
}
