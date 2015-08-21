<?php
namespace Kwf\Trl\Parse;

class ParseAll
{
    protected $_directory;
    protected $_errors;
    protected $_output;
    protected $_ignoredFiles;

    public function __construct($directory, $output)
    {
        $this->_directory = $directory;
        $this->_output = $output;
    }

    public function setIgnoredFiles($paths)
    {
        $this->_ignoredFiles = $paths;
    }

    public function parseDirectoryForTrl()
    {
        // call js parser
        $trlJsParser = new ParseJsForTrl($this->_directory);
        $jsTrls = $trlJsParser->parse($this->_output);

        // call php parser
        $trlPhpParser = new ParsePhpForTrl;
        $trlPhpParser->setIgnoredFiles($this->_ignoredFiles);
        $trlPhpParser->setCodeDirectory($this->_directory);
        $phpTrls = $trlPhpParser->parseCodeDirectory($this->_output);
        $this->_errors = $trlPhpParser->getErrors();
        return array_merge_recursive($jsTrls, $phpTrls);
    }

    public function getErrors()
    {
        return $this->_errors;
    }
}
