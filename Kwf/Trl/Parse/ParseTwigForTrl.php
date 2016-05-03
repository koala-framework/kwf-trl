<?php
namespace Kwf\Trl\Parse;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Helper\ProgressBar;

class ParseTwigForTrl
{
    protected $_fileFinder;
    protected $_directory;

    const ERROR_WRONG_NR_OF_ARGUMENTS = 'wrongNrOfArguments';

    public function __construct($directory)
    {
        $this->_directory = $directory;
        $this->_fileFinder = new Finder();
        $this->_fileFinder->files();
        $this->_fileFinder->in($directory);
        $excludeFolders = array('vendor', 'tests', 'cache', 'node_modules');
        foreach ($excludeFolders as $excludeFolder) {
            $this->_fileFinder->exclude($excludeFolder);
        }
        $this->_fileFinder->name('*.twig');
    }

    private function _createKwfTwigEnvironment()
    {
        \Twig_Autoloader::register();
        $loader = new \Twig_Loader_Filesystem($this->_directory);
        $twig = new \Twig_Environment($loader, array('debug'=>true));

        $twig->addGlobal('renderer', array('ParseTwigForTrl', 'twigFunctionMockup'));

        // Source for this filters: Kwf_View_Twig_Environment
        $twig->addFilter(new \Twig_SimpleFilter('bemClass', array('ParseTwigForTrl', 'twigFunctionMockup')));
        $twig->addFilter(new \Twig_SimpleFilter('date', array('ParseTwigForTrl', 'twigFunctionMockup')));
        $twig->addFilter(new \Twig_SimpleFilter('dateTime', array('ParseTwigForTrl', 'twigFunctionMockup')));
        $twig->addFilter(new \Twig_SimpleFilter('money', array('ParseTwigForTrl', 'twigFunctionMockup')));
        $twig->addFilter(new \Twig_SimpleFilter('mailEncodeText', array('ParseTwigForTrl', 'twigFunctionMockup')));
        $twig->addFilter(new \Twig_SimpleFilter('mailLink', array('ParseTwigForTrl', 'twigFunctionMockup')));
        $twig->addFilter(new \Twig_SimpleFilter('hiddenOptions', array('ParseTwigForTrl', 'twigFunctionMockup')));

        $twig->addFunction('includeCode', new \Twig_SimpleFunction('includeCode',  array('ParseTwigForTrl', 'twigFunctionMockup')));
        return $twig;
    }

    public static function twigFunctionMockup($string)
    {
        return $string;
    }

    public function parse($output)
    {
        $trlElements = array();
        $fileCount = iterator_count($this->_fileFinder);
        $output->writeln('Twig-Files:');
        $progress = new ProgressBar($output, $fileCount);
        $twig = $this->_createKwfTwigEnvironment();
        foreach ($this->_fileFinder as $file) {
            $nodes = $twig->parse($twig->tokenize(file_get_contents($file->getRealpath()), $file->getRealpath()));
            $trlElementsFromFile = array();
            //Recursively loop through the AST
            foreach ( $nodes as $child ) {
                if ($child instanceof \Twig_Node) {
                    $trlElementsFromFile = $this->_process($child, $trlElementsFromFile);
                }
            }
            foreach ($trlElementsFromFile as $trlElement) {
                $trlElement['file'] = $file->getRealpath();
                $trlElements[] = $trlElement;
            }
            $progress->advance();
        }
        $progress->finish();
        $output->writeln('');
        return $trlElements;
    }

    private function _process($node, $trlElements)
    {
        if ($node instanceof \Twig_Node_Expression_GetAttr && $node->getAttribute('type') == 'method') {
            $trlType = false;
            foreach ($node->getIterator() as $childNode) {
                if ($childNode instanceof \Twig_Node_Expression_Constant
                    && strpos($childNode->getAttribute('value'), 'trl') === 0
                ) {
                    $trlType = $childNode->getAttribute('value');
                }
            }
            if ($trlType) {
                $arguments = array();
                foreach ($node->getIterator() as $childNode) {
                    if ($childNode instanceof \Twig_Node_Expression_Array) {
                        foreach ($childNode->getIterator() as $argument) {
                            $arguments[] = $argument;
                        }
                    }
                }
                $trlElement = array(
                    'type' => $trlType,
                    'linenr' => $arguments[0]->getLine()
                );
                if (strpos($trlType, 'Kwf') !== false) {
                    $trlElement['source'] = 'kwf';
                    $trlType = str_replace('KwfStatic', '', $trlType);
                    $trlType = str_replace('Kwf', '', $trlType);
                    $trlElement['type'] = $trlType;
                } else {
                    $trlElement['source'] = 'web';
                }
                $supportedType = false;
                if ($trlType == 'trlcp') {
                    $supportedType = true;
                    if (!in_array(count($arguments), array(8))) {
                        $trlElement['error_short'] = self::ERROR_WRONG_NR_OF_ARGUMENTS;
                    } else {
                        $trlElement['context'] = $arguments[1]->getAttribute('value');
                        $trlElement['text'] = $arguments[3]->getAttribute('value');
                        $trlElement['plural'] = $arguments[5]->getAttribute('value');
                    }
                } else if ($trlType == 'trlc') {
                    $supportedType = true;
                    if (!in_array(count($arguments), array(4, 6))) {
                        $trlElement['error_short'] = self::ERROR_WRONG_NR_OF_ARGUMENTS;
                    } else {
                        $trlElement['context'] = $arguments[1]->getAttribute('value');
                        $trlElement['text'] = $arguments[3]->getAttribute('value');
                    }
                } else if ($trlType == 'trlp') {
                    $supportedType = true;
                    if (!in_array(count($arguments), array(6))) {
                        $trlElement['error_short'] = self::ERROR_WRONG_NR_OF_ARGUMENTS;
                    } else {
                        $trlElement['text'] = $arguments[1]->getAttribute('value');
                        $trlElement['plural'] = $arguments[3]->getAttribute('value');
                    }
                } else if ($trlType == 'trl') {
                    $supportedType = true;
                    if (!in_array(count($arguments), array(2, 4))) {
                        $trlElement['error_short'] = self::ERROR_WRONG_NR_OF_ARGUMENTS;
                    } else {
                        $trlElement['text'] = $arguments[1]->getAttribute('value');
                    }
                }
                if ($supportedType) {
                    $trlElements[] = $trlElement;
                }
            } else {
                foreach ($node->getIterator() as $childNode) {
                    $trlElements = $this->_process($childNode, $trlElements);
                }
            }
        } else if ($node instanceof \Twig_Node) {
            foreach ($node->getIterator() as $childNode) {
                $trlElements = $this->_process($childNode, $trlElements);
            }
        }
        return $trlElements;
    }
}
