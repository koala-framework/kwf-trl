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
                $poElement['msgid_plural'] = $trlElement['plural'];
                $poElement['msgctxt'] = array($trlElement['context']);
                $entryId = $poFile->getEntryId($poElement);
                $poFile->setEntry($entryId, $poElement, true);
            } else if ($trlElement['type'] ==  'trlc') {
                $poElement['msgctxt'] = array($trlElement['context']);
                $entryId = $poFile->getEntryId($poElement);
                $poFile->setEntry($entryId, $poElement, true);
            } else if ($trlElement['type'] == 'trlp') {
                $poElement['msgid_plural'] = $trlElement['plural'];
                $entryId = $poFile->getEntryId($poElement);
                $poFile->setEntry($entryId, $poElement, true);
                $poFile->setEntryPlural($entryId, $trlElement['plural']);
            } else if ($trlElement['type'] == 'trl') {
                $entryId = $poFile->getEntryId($poElement);
                if (isset($poFile->entries()[$entryId])) continue;
                $poFile->setEntry($entryId, $poElement, true);
            }
        }
        return $poFile;
    }
}
