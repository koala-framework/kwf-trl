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

    public function generatePoFileObject()
    {
        $poFile = new \Sepia\PoParser;
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
            if ($trlElement['type'] == 'trlcp') {
                $poFile->updateEntry($trlElement['text'], $trlElement['text'], array(), array(), array(), true);
                $poFile->updateEntryPlural($trlElement['text'], $trlElement['plural']);
                $poFile->updateEntryContext($trlElement['text'], $trlElement['context']);
            } else if ($trlElement['type'] ==  'trlc') {
                $poFile->updateEntry($trlElement['text'], $trlElement['text'], array(), array(), array(), true);
                $poFile->updateEntryContext($trlElement['text'], $trlElement['context']);
            } else if ($trlElement['type'] == 'trlp') {
                $poFile->updateEntry($trlElement['text'], $trlElement['text'], array(), array(), array(), true);
                $poFile->updateEntryPlural($trlElement['text'], $trlElement['plural']);
            } else if ($trlElement['type'] == 'trl') {
                $poFile->updateEntry($trlElement['text'], $trlElement['text'], array(), array(), array(), true);
            }
        }
        return $poFile;
    }
}
