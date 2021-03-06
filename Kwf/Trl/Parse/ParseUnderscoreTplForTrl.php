<?php
namespace Kwf\Trl\Parse;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Helper\ProgressBar;

class ParseUnderscoreTplForTrl {
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
        $this->_fileFinder->name('*.underscore.tpl');
    }

    public function parse($output)
    {
        $trlElements = array();
        $fileCount = iterator_count($this->_fileFinder);
        $output->writeln('UnderscoreTemplate-Files:');
        $progress = new ProgressBar($output, $fileCount);
        foreach ($this->_fileFinder as $file) {
            $progress->advance();
            $trlElements = array_merge($trlElements, \Kwf_TrlJsParser_UnderscoreTemplateParser::parseContent($file->getContents()));
        }
        $progress->finish();
        $output->writeln('');
        return $trlElements;
    }
}
