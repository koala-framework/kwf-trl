<?php
namespace Kwf\Trl\Parse;

class ParsePhpForTrl {
    protected $_parser;
    protected $_codeDirectory;
    protected $_codeContent;

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
    }

    public function setCodeDirectory($dir)
    {
        $this->_codeDirectory = $dir;
    }

    public function setCodeContent($content)
    {
        $this->_codeContent = $content;
    }

    public function parseCodeDirectory()
    {
        return $this->_recursiveParseCodeDirectory($this->_codeDirectory);
    }

    private function _recursiveParseCodeDirectory($directory)
    {
        $translations = array();
        $files = scandir($directory);
        foreach ($files as $file) {
            if ($file == '.') continue;
            if ($file == '..') continue;
            if ($file == '.git') continue;
            if ($file == 'vendor') continue;

            $path = $directory.'/'.$file;
            if (is_dir($path)) {
                $translations = array_merge_recursive($this->_recursiveParseCodeDirectory($path), $translations);
            } else {
                $splited = explode('.', $file);
                $extension = $splited[count($splited)-1];
                if (in_array($extension, array('php', 'tpl'))) {
                    $this->_codeContent = file_get_contents($path);
                    try {
                        $translations = array_merge_recursive($translations, $this->parseContent());
                    } catch (\PhpParser\Error $e) {
                        $this->_exceptions[$path][] = $e->getMessage();
                    }
                }
            }
        }
        return $translations;
    }

    public function parseContent()
    {
        $traverser = new \PhpParser\NodeTraverser;
        $visitor = new ParsePhpForTrlVisitor;
        $traverser->addVisitor($visitor);
        $statments = $traverser->traverse($this->_parser->parse($this->_codeContent));
        return $visitor->getTranslations();
    }
}
