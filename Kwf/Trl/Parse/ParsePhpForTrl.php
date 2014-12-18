<?php
namespace Kwf\Trl\Parse;

class ParsePhpForTrl {
    protected $_parser;
    protected $_codeDirectory;
    protected $_codeContent;
    protected $_exceptions = array();

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

    public function getExceptions()
    {
        return $this->_exceptions;
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
