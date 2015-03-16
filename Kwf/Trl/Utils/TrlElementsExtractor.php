<?php
namespace Kwf\Trl\Utils;

class TrlElementsExtractor
{
    protected $_poFile;
    public function __construct(\Sepia\PoParser $poFile)
    {
        $this->_poFile = $poFile;
    }

    public function extractTrlElements()
    {
        $trlElements = array();
        foreach ($this->_poFile->entries() as $entry) {
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
