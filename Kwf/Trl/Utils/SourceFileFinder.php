<?php
namespace Kwf\Trl\Utils;

class SourceFileFinder {

    protected $_directory;
    protected $_fileTypes = array('js', 'php');
    protected $_ignoreDirs = array();

    public function __construct()
    {
    }

    public function setDirectory($directory)
    {
        $this->_directory = $directory;
    }

    public function setFileTypes($fileTypes)
    {
        $this->_fileTypes = $fileTypes;
    }

    public function setIgnoreDirectories($ignoreDirectories)
    {
        $this->_ignoreDirs = $ignoreDirectories;
    }

    public function getFiles()
    {
        $initCwd = getcwd();
        chdir($this->_directory);
        $files = $this->_glob_recursive('*.{'.implode(',', $this->_fileTypes).'}', GLOB_BRACE);
        chdir($initCwd);
        return $files;
    }

    private function _glob_recursive($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            if (in_array(basename($dir), $this->_ignoreDirs)) continue;
            $files = array_merge($files, $this->_glob_recursive($dir.'/'.basename($pattern), $flags));
        }
        return $files;
    }
}
