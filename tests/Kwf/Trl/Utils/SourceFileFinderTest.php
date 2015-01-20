<?php
use \Kwf\Trl\Utils\SourceFileFinder;

class SourceFileFinderTest extends PHPUnit_Framework_TestCase
{
    public function testReturningCorrectFileTypes()
    {
        $finder = new SourceFileFinder();
        $finder->setFileTypes(array('js', 'php'));
        $finder->setDirectory('tests/Kwf/Trl/Utils/directory');
        $this->assertEquals(array('code.js', 'code.php'), $finder->getFiles());
    }

    public function testReturningFilesRecursively()
    {
        $finder = new SourceFileFinder();
        $finder->setFileTypes(array('js', 'php'));
        $finder->setDirectory('tests/Kwf/Trl/Utils/directory2');
        $this->assertEquals(
            array('code.js', 'code.php', './subdir/code.php', './subdir/subsubdir/code.php'),
            $finder->getFiles()
        );
    }

    public function testIgnoringFolders()
    {
        $finder = new SourceFileFinder();
        $finder->setFileTypes(array('js', 'php'));
        $finder->setIgnoreDirectories(array('ignore', 'ignore2'));
        $finder->setDirectory('tests/Kwf/Trl/Utils/directory3');
        $files = $finder->getFiles();
        $this->assertFalse(in_array('./ignore/code.php', $files));
        $this->assertFalse(in_array('./ignore/code.txt', $files));
        $this->assertFalse(in_array('./dontignore/ignore/code.php', $files));
        $this->assertEquals(1, count($files));
    }
}
