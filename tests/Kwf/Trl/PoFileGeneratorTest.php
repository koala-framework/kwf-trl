<?php
use \Kwf\Trl\Utils\PoFileGenerator;
class PoFileGeneratorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testPluralNotOverriden()
    {
        $testFilePath = 'testFile.po';
        touch($testFilePath);
        $trlElements = array(
            array (
                'type' => 'trl',
                'text' => 'Category',
            ),
            array (
                'type' => 'trlp',
                'text' => 'Category',
                'plural' => 'Categories',
            ),
            array (
                'type' => 'trlp',
                'text' => 'Category',
                'plural' => 'Categories',
            ),
            array (
                'type' => 'trl',
                'text' => 'Category',
            )
        );
        $poFileGenerator = new PoFileGenerator($trlElements);
        $poFile = $poFileGenerator->generatePoFileObject($testFilePath);
        $this->assertEquals(array(
            'Category' => array(
                'msgid' => 'Category',
                'msgstr' => 'Category',
                'msgid_plural' => 'Categories'
            )
        ), $poFile->entries());
        unlink($testFilePath);
    }

    public function testCombinationContextPlural()
    {
        $testFilePath = 'testFile.po';
        touch($testFilePath);
        $trlElements = array(
            array (
                'type' => 'trl',
                'text' => 'Category',
            ),
            array (
                'type' => 'trlp',
                'text' => 'Category',
                'plural' => 'Categories',
            ),
            array (
                'type' => 'trlc',
                'text' => 'Category',
                'context' => 'Test'
            ),
            array(
                'type' => 'trlcp',
                'text' => 'Category',
                'context' => 'Test',
                'plural' => 'Categories2'
            )
        );
        $poFileGenerator = new PoFileGenerator($trlElements);
        $poFile = $poFileGenerator->generatePoFileObject($testFilePath);
        $this->assertEquals(array(
            'Category' => array(
                'msgid' => 'Category',
                'msgstr' => 'Category',
                'msgid_plural' => 'Categories'
            ),
            'Test<##EOC##>Category' => array(
                'msgid' => 'Category',
                'msgstr' => 'Category',
                'msgctxt' => array('Test'),
                'msgid_plural' => 'Categories2'
            )
        ), $poFile->entries());
        unlink($testFilePath);
    }

    public function testWithAndWithoutContextNotOverriden()
    {
        $testFilePath = 'testFile.po';
        touch($testFilePath);
        $trlElements = array(
            array(
                'type' => 'trl',
                'text' => 'Amount'
            ),
            array(
                'type' => 'trlc',
                'context' => 'Amount of Money',
                'text' => 'Amount'
            )
        );
        $poFileGenerator = new PoFileGenerator($trlElements);
        $poFile = $poFileGenerator->generatePoFileObject($testFilePath);
        $this->assertEquals(array(
            'Amount' => array(
                'msgid' => 'Amount',
                'msgstr' => 'Amount'
            ),
            'Amount of Money<##EOC##>Amount' => array(
                'msgid' => 'Amount',
                'msgstr' => 'Amount',
                'msgctxt' => array('Amount of Money')
            )
        ), $poFile->entries());
        unlink($testFilePath);
    }

    public function testDifferentContextNotOverriden()
    {
        $testFilePath = 'testFile.po';
        touch($testFilePath);
        $trlElements = array(
            array (
                'type' => 'trlc',
                'context' => 'salutation firstname male',
                'text' => 'Dear {0}',
            ),
            array (
                'type' => 'trlc',
                'context' => 'salutation firstname female',
                'text' => 'Dear {0}'
            )
        );
        $poFileGenerator = new PoFileGenerator($trlElements);
        $poFile = $poFileGenerator->generatePoFileObject($testFilePath);
        $this->assertTrue(strpos($poFile->compile(), 'salutation firstname male') !== false);
        $this->assertTrue(strpos($poFile->compile(), 'salutation firstname female') !== false);
        unlink($testFilePath);
    }
}
