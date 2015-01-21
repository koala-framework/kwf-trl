<?php
namespace Kwf\Trl\Parse;

use Kwf\Trl\Utils\SourceFileFinder;

class ParsePhpForTrl {
    protected $_parser;
    protected $_codeContent;
    protected $_sourceFileFinder;
    protected $_directory;
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
        $this->_sourceFileFinder = new SourceFileFinder;
        $this->_sourceFileFinder->setFileTypes(array('php', 'tpl'));
        $this->_sourceFileFinder->setIgnoreDirectories(array('git', 'vendor'));
    }

    public function setCodeDirectory($dir)
    {
        $this->_directory = $dir;
        $this->_sourceFileFinder->setDirectory($dir);
    }

    public function setCodeContent($content)
    {
        $this->_codeContent = $content;
    }

    public function parseCodeDirectory()
    {
        $this->_errors = array();
        $trlElements = array();
        $initCwd = getcwd();
        chdir($this->_directory);
        foreach ($this->_sourceFileFinder->getFiles() as $file) {
            $this->_codeContent = file_get_contents($file);
            echo $file."\n";
            try {
                foreach ($this->parseContent() as $trlElementOfFile) {
                    $trlElementOfFile['file'] = $file;
                    $trlElements[] = $trlElementOfFile;
                }
            } catch(\PhpParser\Error $e) {
                $this->_errors[] = array(
                    'error' => $e,
                    'file' => $this->_directory.'/'.$file
                );
            }
        }
        chdir($initCwd);
        return $trlElements;
    }

    public function parseContent()
    {
        $traverser = new \PhpParser\NodeTraverser;
        $visitor = new ParsePhpForTrlVisitor;
        $traverser->addVisitor($visitor);
        $statments = $traverser->traverse($this->_parser->parse($this->_codeContent));
        return $visitor->getTranslations();
    }

    public function getErrors()
    {
        return $this->_errors;
    }
}
