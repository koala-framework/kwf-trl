<?php
namespace Kwf\Trl\Parse;

class ParseJsForTrl {
    protected $_fileFinder;
    protected $_directory;
    public function __construct($directory)
    {
        $this->_directory = $directory;
        $this->_fileFinder = new \Kwf\Trl\Utils\SourceFileFinder;
        $this->_fileFinder->setDirectory($directory);
        $this->_fileFinder->setFileTypes(array('js'));
        $this->_fileFinder->setIgnoreDirectories(array('.git', 'vendor', 'node_modules'));
    }

    public function parse()
    {
        $initCwd = getcwd();
        chdir($this->_directory);
        $trlElements = array();
        foreach ($this->_fileFinder->getFiles() as $file) {
            $trlElements = array_merge($trlElements, \Kwf_Trl_Parser_JsParser::parseContent(file_get_contents($file)));
        }
        chdir($initCwd);
        return $trlElements;
    }
}
