<?php
namespace Kwf\Trl\Utils;

class PoFileGenerator
{
    protected $_trlElements;
    protected $_kwfTrlElements;
    protected $_errors = array();

    public function __construct($trlElements, $kwfTrlElements = array())
    {
        $this->_trlElements = $trlElements;
        $this->_kwfTrlElements = $kwfTrlElements;
    }

    public function generatePoFileObject($filePath)
    {
        $poFile = new \Sepia\PoParser(new \Sepia\FileHandler($filePath));
        foreach ($this->_trlElements as $trlElement) {
            if (isset($trlElement['error_short'])) {
                $this->_errors[] = $trlElement;
                continue;
            }
            // Check if translation is in kwf
            $trlFoundInKwf = false;
            foreach ($this->_kwfTrlElements as $kwfTrlElement) {
                if ($kwfTrlElement['type'] == $trlElement['type']
                    && $kwfTrlElement['text'] == $trlElement['text']
                ) {
                    $trlFoundInKwf = true;
                    break;
                }
            }
            if ($trlFoundInKwf) continue;
            $poElement = array(
                'msgid' => $trlElement['text'],
                'msgstr' => $trlElement['text']
            );
            if ($trlElement['type'] == 'trlcp') {
                $poFile->setEntry($trlElement['text'], $poElement, true);
                $poFile->setEntryPlural($trlElement['text'], $trlElement['plural']);
                $poFile->setEntryContext($trlElement['text'], $trlElement['context']);
            } else if ($trlElement['type'] ==  'trlc') {
                $poFile->setEntry($trlElement['text'], $poElement, true);
                $poFile->setEntryContext($trlElement['text'], $trlElement['context']);
            } else if ($trlElement['type'] == 'trlp') {
                $poFile->setEntry($trlElement['text'], $poElement, true);
                $poFile->setEntryPlural($trlElement['text'], $trlElement['plural']);
            } else if ($trlElement['type'] == 'trl') {
                $poFile->setEntry($trlElement['text'], $poElement, true);
            }
        }
        return $poFile;
    }
}
