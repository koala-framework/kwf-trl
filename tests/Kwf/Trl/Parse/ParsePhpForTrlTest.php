<?php
use \Kwf\Trl\Parse\ParsePhpForTrl;

class ParsePhpForTrlTest extends PHPUnit_Framework_TestCase
{
    private $_parserObject;
    public function setUp()
    {
        parent::setUp();
        $this->_parserObject = new ParsePhpForTrl;
    }

    public function testTrlMethodCalls()
    {
        $content = "<?php \$trl->trl('testWord');";
        $this->_parserObject->setCodeContent($content);
        $values = $this->_parserObject->parseContent();

        $content = "<?php \$trl->trlc('testWord');";
        $this->_parserObject->setCodeContent($content);
        $values = $this->_parserObject->parseContent();

        $content = "<?php \$trl->trlp('testWord');";
        $this->_parserObject->setCodeContent($content);
        $values = $this->_parserObject->parseContent();

        $content = "<?php \$trl->trlcp('testWord');";
        $this->_parserObject->setCodeContent($content);
        $values = $this->_parserObject->parseContent();


        $content = "<?php \$trl->trlKwf('testWord');";
        $this->_parserObject->setCodeContent($content);
        $values = $this->_parserObject->parseContent();

        $content = "<?php \$trl->trlcKwf('testWord');";
        $this->_parserObject->setCodeContent($content);
        $values = $this->_parserObject->parseContent();

        $content = "<?php \$trl->trlpKwf('testWord');";
        $this->_parserObject->setCodeContent($content);
        $values = $this->_parserObject->parseContent();

        $content = "<?php \$trl->trlcpKwf('testWord');";
        $this->_parserObject->setCodeContent($content);
        $values = $this->_parserObject->parseContent();
    }

    public function testTrlParsePhpStatic()
    {
        $this->_parserObject->setCodeContent("<?php trlStatic('testWord');");
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('web', $values[0]['source']);
        $this->assertEquals('testWord', $values[0]['text']);

        $this->_parserObject->setCodeContent("<?php trlcStatic(\"testContext\", \"testWord\");");
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('web', $values[0]['source']);
        $this->assertEquals('testWord', $values[0]['text']);
        $this->assertEquals('testContext', $values[0]['context']);

        $this->_parserObject->setCodeContent("<?php trlpStatic('testWord', 'testWords', 3);");
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('web', $values[0]['source']);
        $this->assertEquals('testWord', $values[0]['text']);
        $this->assertEquals('testWords', $values[0]['plural']);

        $this->_parserObject->setCodeContent("<?php trlcpStatic('testContext', 'testWord', 'testWords', 3);");
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('web', $values[0]['source']);
        $this->assertEquals('testWord', $values[0]['text']);
        $this->assertEquals('testWords', $values[0]['plural']);
        $this->assertEquals('testContext', $values[0]['context']);
        $this->assertEquals('trlcp', $values[0]['type']);

        $this->_parserObject->setCodeContent("<?php trlKwfStatic('testWord');");
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('kwf', $values[0]['source']);
        $this->assertEquals('testWord', $values[0]['text']);

        $this->_parserObject->setCodeContent("<?php trlcKwfStatic(\"testContext\", \"testWord\");");
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('kwf', $values[0]['source']);
        $this->assertEquals('testWord', $values[0]['text']);
        $this->assertEquals('testContext', $values[0]['context']);

        $this->_parserObject->setCodeContent("<?php trlpKwfStatic('testWord', 'testWords', 3);");
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('kwf', $values[0]['source']);
        $this->assertEquals('testWord', $values[0]['text']);
        $this->assertEquals('testWords', $values[0]['plural']);

        $this->_parserObject->setCodeContent("<?php trlcpKwfStatic('testContext', 'testWord', 'testWords', 3);");
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('kwf', $values[0]['source']);
        $this->assertEquals('testWord', $values[0]['text']);
        $this->assertEquals('testWords', $values[0]['plural']);
        $this->assertEquals('testContext', $values[0]['context']);
        $this->assertEquals('trlcp', $values[0]['type']);
    }

    public function testTrlParsePhp2()
    {
        $this->_parserObject->setCodeContent('<?php trl("\n");');
        $values = $this->_parserObject->parseContent();
        $this->assertEquals(ParsePhpForTrl::ERROR_INVALID_STRING, $values[0]['error_short']);

        $this->_parserObject->setCodeContent('<?php trl("");');
        $values = $this->_parserObject->parseContent();
        $this->assertEquals(ParsePhpForTrl::ERROR_INVALID_STRING, $values[0]['error_short']);

        $this->_parserObject->setCodeContent('<?php trlc("aaa");');
        $values = $this->_parserObject->parseContent();
        $this->assertEquals(ParsePhpForTrl::ERROR_WRONG_NR_OF_ARGUMENTS, $values[0]['error_short']);

        $this->_parserObject->setCodeContent('<?php trlc("aaa", array("hallo"));');
        $values = $this->_parserObject->parseContent();
        $this->assertEquals(ParsePhpForTrl::ERROR_WRONG_ARGUMENT_TYPE, $values[0]['error_short']);

        $this->_parserObject->setCodeContent('<?php trl("hallo");
        trlc("context", "text");
        trlp("one beer", "{0} beers", 5); $asdfjklkasjf; $asklfjdksadljf; trl("asdf"."asdf").$asklfjsdalkfj; trl("test");');
        $values = $this->_parserObject->parseContent();
        $this->assertEquals("hallo", $values[0]['text']);
        $this->assertEquals("context", $values[1]['context']);
        $this->assertEquals("text", $values[1]['text']);
        $this->assertEquals("one beer", $values[2]['text']);
        $this->assertEquals("{0} beers", $values[2]['plural']);
//TODO fix properly         $this->assertEquals(Kwf_Trl::ERROR_INVALID_CHAR, $values[3]['error_short']);
        $this->assertEquals("test", $values[4]['text']);

        $this->_parserObject->setCodeContent('<?php trl("test"."foo");');
        $values = $this->_parserObject->parseContent();
        $this->assertEquals(ParsePhpForTrl::ERROR_WRONG_ARGUMENT_TYPE, $values[0]['error_short']);

        $this->_parserObject->setCodeContent('<?php trl("test$foo");');
        $values = $this->_parserObject->parseContent();
        $this->assertEquals(ParsePhpForTrl::ERROR_WRONG_ARGUMENT_TYPE, $values[0]['error_short']);

        $this->_parserObject->setCodeContent('<?php trl("test\$foo");');
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('test$foo', $values[0]['text']);


        $this->_parserObject->setCodeContent('<?php $a = 100;
        if ($a == 100) {
            $a += 200;
        }

        trl(\'check\');
        file_get_contents(\'Abcdefg\');

        $asdfsafd = 0;
        trl(\'test$foo\');');
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('check', $values[0]['text']);
        $this->assertEquals(6, $values[0]['linenr']);
        $this->assertEquals('test$foo', $values[1]['text']);
        $this->assertEquals(10, $values[1]['linenr']);

        //gleicher test mit fehler
        $this->_parserObject->setCodeContent('<?php $asdfdsa;
        $fasdfasdf;
        $asdf = a;
        $s;
        trl(\'check\');
        $asfsdafa;

        $asdfsafd;
        trl("test"."foo");');
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('check', $values[0]['text']);
        $this->assertEquals(5, $values[0]['linenr']);
        $this->assertEquals(9, $values[1]['linenr']);

        $this->_parserObject->setCodeContent("<?php trl(\"test\nfoo\");");
        $values = $this->_parserObject->parseContent();
        $this->assertEquals("test\nfoo", $values[0]['text']);

        $this->_parserObject->setCodeContent("<?php trl('testWord');");
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('web', $values[0]['source']);
        $this->assertEquals('testWord', $values[0]['text']);

        $this->_parserObject->setCodeContent("<?php trlKwf('testWord');");
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('kwf', $values[0]['source']);
        $this->assertEquals('testWord', $values[0]['text']);

        $this->_parserObject->setCodeContent("<?php trlc(\"testContext\", \"testWord\");");
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('web', $values[0]['source']);
        $this->assertEquals('testWord', $values[0]['text']);
        $this->assertEquals('testContext', $values[0]['context']);

        $this->_parserObject->setCodeContent("<?php trlcKwf(\"testContext\", \"testWord\");");
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('kwf', $values[0]['source']);
        $this->assertEquals('testWord', $values[0]['text']);
        $this->assertEquals('testContext', $values[0]['context']);

        $this->_parserObject->setCodeContent("<?php trlp('testWord', 'testWords', 3);");
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('web', $values[0]['source']);
        $this->assertEquals('testWord', $values[0]['text']);
        $this->assertEquals('testWords', $values[0]['plural']);

        $this->_parserObject->setCodeContent("<?php trlpKwf('testWord', 'testWords', 3);");
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('kwf', $values[0]['source']);
        $this->assertEquals('testWord', $values[0]['text']);
        $this->assertEquals('testWords', $values[0]['plural']);

        $this->_parserObject->setCodeContent("<?php trlcpKwf('testContext', 'testWord', 'testWords', 3);");
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('kwf', $values[0]['source']);
        $this->assertEquals('testWord', $values[0]['text']);
        $this->assertEquals('testWords', $values[0]['plural']);
        $this->assertEquals('testContext', $values[0]['context']);
        $this->assertEquals('trlcp', $values[0]['type']);

        //more complicated tests
        $this->_parserObject->setCodeContent("<?php trl('testWord {0} and {1}', array('word1' and 'word2'));");
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('web', $values[0]['source']);
        $this->assertEquals('testWord {0} and {1}', $values[0]['text']);

        $this->_parserObject->setCodeContent('<?php trl("te\'st");');
        $values = $this->_parserObject->parseContent();
        $this->assertEquals("te'st", $values[0]['text']);

        $this->_parserObject->setCodeContent('<?php trl("testW\"ord {0} and {1}");');
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('testW"ord {0} and {1}', $values[0]['text']);

        $this->_parserObject->setCodeContent("<?php trl('te\"st');");
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('te"st', $values[0]['text']);

        $this->_parserObject->setCodeContent("<?php trl('test');");
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('test', $values[0]['text']);

        $this->_parserObject->setCodeContent('<?php trl(\'test\');');
        $values = $this->_parserObject->parseContent();
        $this->assertEquals('test', $values[0]['text']);

        $this->_parserObject->setCodeContent('<?php trl("testWord {0} and {1}", array("word1", "word2"));');
        $values = $this->_parserObject->parseContent();
        $this->assertEquals("testWord {0} and {1}", $values[0]['text']);

        $this->_parserObject->setCodeContent('<?php trlc("context", "text"); trl("text2");');
        $values = $this->_parserObject->parseContent();
        $this->assertEquals("context", $values[0]['context']);
    }
}
