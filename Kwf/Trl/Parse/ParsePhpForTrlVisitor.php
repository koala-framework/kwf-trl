<?php
namespace Kwf\Trl\Parse;

class ParsePhpForTrlVisitor extends \PhpParser\NodeVisitorAbstract
{
    protected $_translations = array(
        'trl' => array(),
        'trlc' => array(),
        'trlp' => array(),
        'trlcp' => array(),

        'trlKwf' => array(),
        'trlcKwf' => array(),
        'trlpKwf' => array(),
        'trlcpKwf' => array(),

        'trlStatic' => array(),
        'trlcStatic' => array(),
        'trlpStatic' => array(),
        'trlcpStatic' => array(),

        'trlKwfStatic' => array(),
        'trlcKwfStatic' => array(),
        'trlpKwfStatic' => array(),
        'trlcpKwfStatic' => array()
    );

    public function enterNode(\PhpParser\Node $node)
    {
        if ($node instanceof \PhpParser\Node\Expr\FuncCall || $node instanceof \PhpParser\Node\Expr\MethodCall) {
            if (!($node->name instanceof \PhpParser\Node\Name)) return;

            $functionName = (string)$node->name;
            if (array_key_exists($functionName, $this->_translations)) {
                if (strpos($functionName, 'trlcp') !== false) {
                    $this->_translations[$functionName][] = array(
                        'context' => $node->args[0]->value->value,
                        'single' => $node->args[1]->value->value,
                        'plural' => $node->args[2]->value->value,
                    );
                } else if (strpos($functionName, 'trlc') !== false) {
                    $this->_translations[$functionName][] = array(
                        'context' => $node->args[0]->value->value,
                        'msg' => $node->args[1]->value->value,
                    );
                } else if (strpos($functionName, 'trlp') !== false) {
                    $this->_translations[$functionName][] = array(
                        'single' => $node->args[0]->value->value,
                        'plural' => $node->args[1]->value->value,
                    );
                } else if (strpos($functionName, 'trl') !== false) {
                    $this->_translations[$functionName][] = $node->args[0]->value->value;
                }
            }
        }
    }

    public function getTranslations()
    {
        return $this->_translations;
    }
}
