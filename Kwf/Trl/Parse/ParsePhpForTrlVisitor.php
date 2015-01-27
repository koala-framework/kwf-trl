<?php
namespace Kwf\Trl\Parse;

class ParsePhpForTrlVisitor extends \PhpParser\NodeVisitorAbstract
{
    protected $_trlElements = array();

    public function enterNode(\PhpParser\Node $node)
    {
        if ($node instanceof \PhpParser\Node\Expr\FuncCall || $node instanceof \PhpParser\Node\Expr\MethodCall) {
            if (!($node->name instanceof \PhpParser\Node\Name)) return;

            $functionName = (string)$node->name;
            $trlElement = null;
            if ($functionName == 'trlcp' || $functionName == 'trlcpStatic'
                || $functionName == 'trlcpKwf' || $functionName == 'trlcpKwfStatic'
            ) {
                $trlElement = array('type' => 'trlcp');
                if (count($node->args) != 3 && count($node->args) != 4) {
                    $trlElement['error_short'] = ParsePhpForTrl::ERROR_WRONG_NR_OF_ARGUMENTS;
                } else if ($node->args[0]->value->getType() != 'Scalar_String'
                    || $node->args[1]->value->getType() != 'Scalar_String'
                    || $node->args[2]->value->getType() != 'Scalar_String'
                ) {
                    $trlElement['error_short'] = ParsePhpForTrl::ERROR_WRONG_ARGUMENT_TYPE;
                } else {
                    $trlElement['text'] = $node->args[1]->value->value;
                    $trlElement['context'] = $node->args[0]->value->value;
                    $trlElement['plural'] = $node->args[2]->value->value;
                    $trlElement['before'] = $functionName.'('.'\''.$node->args[0]->value->value.'\', '
                                                 .'\''.$node->args[1]->value->value.'\', '
                                                 .'\''.$node->args[2]->value->value.'\', '
                                                 .$node->args[3]->value->value
                                                 .')';
                }
            } else if ($functionName == 'trlc' || $functionName == 'trlcStatic'
                || $functionName == 'trlcKwf' || $functionName == 'trlcKwfStatic'
            ) {
                $trlElement = array('type' => 'trlc');
                if (count($node->args) != 2 && count($node->args) != 3) {
                    $trlElement['error_short'] = ParsePhpForTrl::ERROR_WRONG_NR_OF_ARGUMENTS;
                } else if ($node->args[0]->value->getType() != 'Scalar_String'
                    || $node->args[1]->value->getType() != 'Scalar_String'
                ) {
                    $trlElement['error_short'] = ParsePhpForTrl::ERROR_WRONG_ARGUMENT_TYPE;
                } else {
                    $trlElement['context'] = $node->args[0]->value->value;
                    $trlElement['text'] = $node->args[1]->value->value;
                    $trlElement['before'] = $functionName.'('.'\''.$node->args[0]->value->value.'\', '
                                                 .'\''.$node->args[1]->value->value.'\''
                                                 .')';
                }
            } else if ($functionName == 'trlp' || $functionName == 'trlpStatic'
                || $functionName == 'trlpKwf' || $functionName == 'trlpKwfStatic'
            ) {
                $trlElement = array('type' => 'trlp');
                if (count($node->args) != 3) {
                    $trlElement['error_short'] = ParsePhpForTrl::ERROR_WRONG_NR_OF_ARGUMENTS;
                } else if($node->args[0]->value->getType() != 'Scalar_String'
                    || $node->args[1]->value->getType() != 'Scalar_String'
                ) {
                    $trlElement['error_short'] = ParsePhpForTrl::ERROR_WRONG_ARGUMENT_TYPE;
                } else {
                    $trlElement['text'] = $node->args[0]->value->value;
                    $trlElement['plural'] = $node->args[1]->value->value;
                    $trlElement['before'] = $functionName.'('.'\''.$node->args[0]->value->value.'\', '
                                                 .'\''.$node->args[1]->value->value.'\', '
                                                 .$node->args[2]->value->value
                                                 .')';
                }
            } else if ($functionName == 'trl' || $functionName == 'trlStatic'
                || $functionName == 'trlKwf' || $functionName == 'trlKwfStatic'
            ) {
                $trlElement = array('type' => 'trl');
                if (count($node->args) != 1 && count($node->args) != 2) {
                    $trlElement['error_short'] = ParsePhpForTrl::ERROR_WRONG_NR_OF_ARGUMENTS;
                } else if($node->args[0]->value->getType() != 'Scalar_String') {
                    $trlElement['error_short'] = ParsePhpForTrl::ERROR_WRONG_ARGUMENT_TYPE;
                } else {
                    $trlElement['text'] = $node->args[0]->value->value;
                    $trlElement['before'] = $functionName.'(\''.$node->args[0]->value->value.'\')';
                }
            }
            if ($trlElement) {
                $trlElement['linenr'] = $node->getLine();
                $trlElement['source'] = strpos($functionName, 'Kwf') !== false ? 'kwf' : 'web';
                if (isset($trlElement['text'])
                    && ($trlElement['text'] == "\n" || $trlElement['text'] == "")
                ) {
                    $trlElement['error_short'] = ParsePhpForTrl::ERROR_INVALID_STRING;
                }
                $this->_trlElements[] = $trlElement;
            }
        }
    }

    public function getTranslations()
    {
        return $this->_trlElements;
    }
}
