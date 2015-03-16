<?php
namespace Kwf\Trl\Convert;

class TrlXmlToPoConverter
{
    protected $_xmlDocument;
    protected $_poContent;
    protected $_baseLanguage;
    protected $_targetLanguage;

    //https://www.gnu.org/software/gettext/manual/html_node/PO-Files.html
    public function __construct()
    {
    }

    public function setBaseLanguage($language)
    {
        $this->_baseLanguage = $language;
    }
    public function setTargetLanguage($language)
    {
        $this->_targetLanguage = $language;
    }
    public function setXmlPath($xmlPath)
    {
        $this->setXmlContent(file_get_contents($xmlPath));
    }
    public function setXmlContent($content) // needed for testing
    {
        $this->_xmlDocument = simplexml_load_string($content);
    }

    public function convertToPo($output)
    {
        $this->_poContent = array();
        foreach ($this->_xmlDocument->text as $trl) {
            if ($trl->context) {
                $this->_poContent[] = 'msgctxt "'.$trl->context.'"';
            }
            if ($trl->{$this->_baseLanguage}) {
                $this->_poContent[] = 'msgid "'.$this->_escapeString($trl->{$this->_baseLanguage}).'"';
            } else {
                $output->writeln('No value for baseLanguage. Maybe this string does not exist in web or it\'s the wrong baseLanguage.');
            }
            if ($trl->{$this->_baseLanguage.'_plural'}) {
                $this->_poContent[] = 'msgid_plural "'.$this->_escapeString($trl->{$this->_baseLanguage.'_plural'}).'"';
                if ($this->_targetLanguage) {
                    $this->_poContent[] = 'msgstr[0] "'.$this->_escapeString($trl->{$this->_targetLanguage}).'"';
                    $this->_poContent[] = 'msgstr[1] "'.$this->_escapeString($trl->{$this->_targetLanguage.'_plural'}).'"';
                } else {
                    $this->_poContent[] = 'msgstr[0] ""';
                    $this->_poContent[] = 'msgstr[1] ""';
                }
            } else {
                if ($this->_targetLanguage) {
                    $this->_poContent[] = 'msgstr "'.$this->_escapeString($trl->{$this->_targetLanguage}).'"';
                } else {
                    $this->_poContent[] = 'msgstr ""';
                }
            }
            $this->_poContent[] = '';
        }
    }

    private function _escapeString($string)
    {
        return str_replace("\'", "'", str_replace('"', '\"', $string));
    }

    public function getPoContent() // needed for testing
    {
        return $this->_poContent;
    }
    public function writePoContent($poPath)
    {
        return file_put_contents($poPath, implode("\n", $this->_poContent));
    }
}
