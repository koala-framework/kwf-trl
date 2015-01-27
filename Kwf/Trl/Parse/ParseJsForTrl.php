<?php
namespace Kwf\Trl\Parse;
use Symfony\Component\Finder\Finder;

class ParseJsForTrl {
    protected $_fileFinder;
    public function __construct($directory)
    {
        $this->_fileFinder = new Finder();
        $this->_fileFinder->files();
        $this->_fileFinder->in($directory);
        $excludeFolders = array('vendor', 'tests', 'cache', 'node_modules');
        foreach ($excludeFolders as $excludeFolder) {
            $this->_fileFinder->exclude($excludeFolder);
        }
        $this->_fileFinder->name('*.js');
    }

    public function parse()
    {
        $trlElements = array();
        foreach ($this->_fileFinder as $file) {
            $trlElements = array_merge($trlElements, \Kwf_Trl_Parser_JsParser::parseContent($file->getContents()));
        }
        return $trlElements;
    }
}
