<?php
namespace Kwf\Trl\Parse;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Helper\ProgressBar;

class ParseJsForTrl {
    protected $_fileFinder;
    public function __construct($directory)
    {
        $this->_fileFinder = new Finder();
        $this->_fileFinder->files();
        $this->_fileFinder->in($directory);
        $excludeFolders = array('vendor', 'tests', 'cache', 'node_modules', 'node_modules_build');
        foreach ($excludeFolders as $excludeFolder) {
            $this->_fileFinder->exclude($excludeFolder);
        }
        $this->_fileFinder->name('/\.*\.(js|jsx)$/');
    }

    public function parse($output)
    {
        $trlElements = array();
        $fileCount = iterator_count($this->_fileFinder);
        $output->writeln('JS-Files:');
        $progress = new ProgressBar($output, $fileCount);
        foreach ($this->_fileFinder as $file) {
            $progress->advance();

            $isJsx = $file->getExtension() === 'jsx';
            $trlElements = array_merge($trlElements, \Kwf_TrlJsParser_JsParser::parseContent($file->getContents(), $isJsx));
        }
        $progress->finish();
        $output->writeln('');
        return $trlElements;
    }
}
