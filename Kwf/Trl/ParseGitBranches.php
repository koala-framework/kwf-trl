<?php
namespace Kwf\Trl;

use Gitonomy\Git\Repository;
use Kwf\Trl\Parse\ParseAll;
use Kwf\Trl\Utils\PoFileGenerator;
use Kwf\Trl\Utils\TrlElementsExtractor;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class ParseGitBranches
{
    protected $_useKwfPoFile;
    protected $_directory;
    protected $_poFilePath;
    protected $_source;

    protected $_ignoredFiles = array();

    protected $_output;

    function __construct($directory, $poFilePath, $source, OutputInterface $output, $useKwfPoFile = false, $branches = null)
    {
        $this->_useKwfPoFile = $useKwfPoFile;
        $this->_directory = $directory;
        $this->_poFilePath = $poFilePath;
        $this->_source = $source;
        $this->_output = $output;
        $this->_branches = $branches;
    }

    public function setIgnoredFiles($paths)
    {
        $this->_ignoredFiles = $paths;
    }

    public function parse()
    {
        $repositoryOptions = array(
            'logger' => new ConsoleLogger($this->_output),
            'environment_variables' => $_SERVER
        );

        $kwfTrlElements = array();
        if ($this->_useKwfPoFile) {
            $this->_output->writeln('<info>Reading kwf-po file</info>');
            $kwfPoFile = \Sepia\PoParser::parseString(file_get_contents('http://trl.koala-framework.org/api/v1/vivid-planet/koala-framework/en'));
            $trlElementsExtractor = new TrlElementsExtractor($kwfPoFile);
            $kwfTrlElements = $trlElementsExtractor->extractTrlElements();
        }

        $initDir = getcwd();
        $this->_output->writeln('<info>Changing into directory</info>');
        $repository = new Repository($this->_directory, $repositoryOptions);
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
        $repository = new Repository($this->_directory, $repositoryOptions);
        $repository->run('fetch', array('origin'));
        if ($this->_useKwfPoFile) {
            $this->_output->writeln('<info>Iterating over branches matching "^[1-9]+.[0-9]+$"</info>');
        } else {
            $this->_output->writeln('<info>Iterating over branches matching "^[3-9]+.[0-9]+$" and >=3.9</info>');
        }

        if ($this->_branches) {
            $branches = $this->_branches;
        } else {
            $branches = $repository->getReferences()->getBranches();
        }
        foreach ($branches as $branch) {
            $branchName = $this->_branches ? 'origin/' . $branch : $branch->getName();
            if (strpos($branchName, 'origin/') === false) continue;
            // package + kwf parse only versionNumber, production and master branches
            // web should parse all branches (like feature-branches and separate-web-branches)
            if ($this->_source != 'web' && !$this->_branches) {
                $splited = explode('/', $branchName);
                $isVersionNumber = preg_match('/^[0-9]+.[0-9]+$/i', $splited[1]);
                if (sizeof($splited) >= 3) {
                    if ($this->_output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                        $this->_output->writeln("skip branch $branchName, doesn't look like a version number");
                    }
                    continue;
                }
                if (!$isVersionNumber && $splited[1] != 'master' && $splited[1] != 'production') {
                    if ($this->_output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                        $this->_output->writeln("skip branch $branchName, doesn't look like a version number");
                    }
                    continue;
                }

                if (!$this->_useKwfPoFile && $isVersionNumber && version_compare($splited[1], '3.9', '<')) {
                    if ($this->_output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                        $this->_output->writeln("skip branch $branchName, < 3.9");
                    }
                    continue;
                }
            }

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

        $sources = array();
        $filteredTrlElements = array();
        foreach ($trlElements as $trlElement) {
            $sources[$trlElement['source']] = true;
            if ($trlElement['source'] == $this->_source) {
                $filteredTrlElements[] = $trlElement;
            }
        }
        $trlElements = $filteredTrlElements;

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
            $this->_output->writeln('Parse-Errors:');
            foreach ($errors as $error) {
                $this->_output->writeln('  Branch: '.$error['branch'].' | File: '.$error['file']);
                $this->_output->writeln('    Error:'.$error['error']->getMessage());
                $this->_output->writeln('  -------------------------------------');
            }
        }

        $this->_output->writeln('');
        $this->_output->writeln('Every branch has been parsed an data is collected in '.$this->_poFilePath);
        $this->_output->writeln('');
        $this->_output->writeln('1. Please check the mentioned Parse-Errors');
        $this->_output->writeln('2. Upload and translate file from '.$this->_poFilePath.' to lingohub or translate in another way');
        $this->_output->writeln('3. When using lingohub call "./vendor/bin/lingohub downloadTranslations" after translating');
        $this->_output->writeln('');
    }
}
