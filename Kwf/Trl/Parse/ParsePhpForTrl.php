<?php
namespace Kwf\Trl\Parse;

use Symfony\Component\Finder\Finder;

class ParsePhpForTrl {
    protected $_parser;
    protected $_codeContent;
    protected $_fileFinder;
    protected $_errors;

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

    public function setCodeDirectory($dir)
    {
        $this->_fileFinder->in($dir);
    }

    public function setCodeContent($content)
    {
        $this->_codeContent = $content;
    }

    public function parseCodeDirectory()
    {
        $this->_errors = array();
        $trlElements = array();
        foreach ($this->_fileFinder as $file) {
            $this->_codeContent = $file->getContents();
            try {
                foreach ($this->parseContent() as $trlElementOfFile) {
                    $trlElementOfFile['file'] = $file;
                    $trlElements[] = $trlElementOfFile;
                }
            } catch(\PhpParser\Error $e) {
                $this->_errors[] = array(
                    'error' => $e,
                    'file' => $file->getRealPath()
                );
            }
        }
        return $trlElements;
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
