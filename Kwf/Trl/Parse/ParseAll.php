<?php
namespace Kwf\Trl\Parse;

class ParseAll
{
    protected $_directory;
    protected $_errors;

    public function __construct($directory)
    {
        $this->_directory = $directory;
    }

    public function parseDirectoryForTrl()
    {
        // call js parser
        $trlJsParser = new ParseJsForTrl($this->_directory);
        $jsTrls = $trlJsParser->parse();

        // call php parser
        $trlPhpParser = new ParsePhpForTrl;
        $trlPhpParser->setCodeDirectory($this->_directory);
        $phpTrls = $trlPhpParser->parseCodeDirectory();
        $this->_errors = $trlPhpParser->getErrors();
        return array_merge_recursive($jsTrls, $phpTrls);
    }

    public function getErrors()
    {
        return $this->_errors;
    }
}
