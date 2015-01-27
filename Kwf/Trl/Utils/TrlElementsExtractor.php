<?php
namespace Kwf\Trl\Utils;

class TrlElementsExtractor
{
    protected $_poFileObject;
    public function __construct($poFileObject)
    {
        $this->_poFileObject = $poFileObject;
    }

    public function extractTrlElements()
    {
        $trlElements = array();
        foreach ($this->_poFileObject->entries() as $entry) {
            $trlElement = array(
                'text' => $entry['msgid'][0]
            );
            if (isset($entry['msgctxt']) && isset($entry['msgid_plural'])) {
                $trlElement['type'] = 'trlcp';
            } else if (isset($entry['msgctxt'])) {
                $trlElement['type'] = 'trlc';
            } else if (isset($entry['msgid_plural'])) {
                $trlElement['type'] = 'trlp';
            } else {
                $trlElement['type'] = 'trl';
            }
            $trlElements[] = $trlElement;
        }
        return $trlElements;
    }
}
