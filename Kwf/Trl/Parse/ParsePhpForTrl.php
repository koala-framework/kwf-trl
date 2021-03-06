<?php
namespace Kwf\Trl\Parse;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Helper\ProgressBar;

class ParsePhpForTrl {
    protected $_parser;
    protected $_codeContent;
    protected $_fileFinder;
    protected $_errors;
    protected $_ignoredFiles;

    const ERROR_INVALID_STRING = 'invalidString';
    const ERROR_WRONG_NR_OF_ARGUMENTS = 'wrongNrOfArguments';
    const ERROR_WRONG_ARGUMENT_TYPE = 'wrongArgumentType';

    protected $_errorMessages = array(
        self::ERROR_INVALID_STRING => 'String is not valid. Unallowed characters are used',
        self::ERROR_WRONG_NR_OF_ARGUMENTS => 'To few arguments.',
        self::ERROR_WRONG_ARGUMENT_TYPE => 'Wrong argument supplied'
    );

    public function __construct($parser=null)
    {
        $this->_parser = $parser ? $parser : new \PhpParser\Parser(new \PhpParser\Lexer);
        $this->_fileFinder = new Finder();
        $excludeFolders = array('vendor', 'tests', 'cache', 'node_modules');
        foreach ($excludeFolders as $excludeFolder) {
            $this->_fileFinder->exclude($excludeFolder);
        }
        $this->_fileFinder->name('/\.*\.(php|tpl)$/');
        $this->_fileFinder->files();
    }

    public function setIgnoredFiles($paths)
    {
        $this->_ignoredFiles = $paths;
    }

    public function setCodeDirectory($dir)
    {
        $this->_fileFinder->in($dir);
    }

    public function setCodeContent($content)
    {
        $this->_codeContent = $content;
    }

    public function parseCodeDirectory($output)
    {
        $this->_errors = array();
        $trlElements = array();
        $fileCount = iterator_count($this->_fileFinder);
        $output->writeln('PHP-Files:');
        $progress = new ProgressBar($output, $fileCount);
        foreach ($this->_fileFinder as $file) {
            if (in_array($file, $this->_ignoredFiles)) continue;
            $progress->advance();
            $this->_codeContent = $this->_replaceShortOpenTags($file->getContents());
            try {
                foreach ($this->parseContent() as $trlElementOfFile) {
                    $trlElementOfFile['file'] = $file->getRealpath();
                    $trlElements[] = $trlElementOfFile;
                }
            } catch(\PhpParser\Error $e) {
                $this->_errors[] = array(
                    'error' => $e,
                    'file' => $file->getRealPath()
                );
            }
        }
        $progress->finish();
        $output->writeln('');
        return $trlElements;
    }

    public function _replaceShortOpenTags($content)
    {
        return preg_replace('#<\?(?!php|=)#', '<?php ', $content);
    }

    public function parseContent()
    {
        $traverser = new \PhpParser\NodeTraverser;
        $visitor = new ParsePhpForTrlVisitor;
        $traverser->addVisitor($visitor);
        ini_set('xdebug.max_nesting_level', 200);
        $statments = $traverser->traverse($this->_parser->parse($this->_codeContent));
        return $visitor->getTranslations();
    }

    public function getErrors()
    {
        return $this->_errors;
    }
}
