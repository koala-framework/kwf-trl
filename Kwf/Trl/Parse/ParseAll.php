<?php
namespace Kwf\Trl\Parse;

class ParseAll
{
    protected $_directory;
    protected $_errors = array();
    protected $_output;
    protected $_ignoredFiles = array();

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
        // call twig parser
        $twigParser = new ParseTwigForTrl($this->_directory);
        $twigTrls = $twigParser->parse($this->_output);

        // call js parser
        $trlJsParser = new ParseJsForTrl($this->_directory);
        $jsTrls = $trlJsParser->parse($this->_output);
        $this->_errors = array_merge($this->_errors, $trlJsParser->getErrors());

        $trlUnderscoreTplParser = new ParseUnderscoreTplForTrl($this->_directory);
        $underscoreTplTrls = $trlUnderscoreTplParser->parse($this->_output);

        // call php parser
        $trlPhpParser = new ParsePhpForTrl;
        $trlPhpParser->setIgnoredFiles($this->_ignoredFiles);
        $trlPhpParser->setCodeDirectory($this->_directory);
        $phpTrls = $trlPhpParser->parseCodeDirectory($this->_output);
        $this->_errors = array_merge($this->_errors, $trlPhpParser->getErrors());
        return array_merge_recursive($jsTrls, $phpTrls, $twigTrls, $underscoreTplTrls);
    }

    public function getErrors()
    {
        return $this->_errors;
    }
}
