<?php
namespace Kwf\Trl;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TrlCollectCommand extends Command
{
    protected function configure()
    {
        $this->setName('trlCollect')
            ->setDescription('Parse every branch for trl and collect them into a single file')
            ->addArgument('path', InputArgument::OPTIONAL, 'Path for po-file', 'trl.po')
            ->addArgument('mask', InputArgument::OPTIONAL, 'Mask to parse for. This can be trl or trlKwf', 'trlKwf')
            ->addArgument('dir', InputArgument::OPTIONAL, 'Path to source directory', '.')
            ->addArgument('kwf', InputArgument::OPTIONAL, 'Path to kwf directory (only if parsing package)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $initCwd = getcwd();
        $sourceDir = $input->getArgument('dir');

        chdir($sourceDir);
        exec('git branch', $branches, $ret);
        if ($ret != 0) {
            $output->writeln('<error>Directory is no git repository</error>');
            exit(1);
        }

        // get current branch to restore afterwards
        $initBranch;
        foreach ($branches as $branch) {
            if (strpos($branch, '*') !== false) {
                $initBranch = str_replace('*', ' ', $branch);
                break;
            }
        }

        // iterate over branches and parse code
        foreach ($branches as $branch) {
            if (strpos($branch, '-') !== false || strpos($branch, 'production') !== false) {
                continue;
            }

            $branch = str_replace('*', '', str_replace(' ', '', $branch));
            $output->writeln('checkout branch: '.$branch);
            $cmdOutput = null;
            exec('git checkout '.$branch, $cmdOutput, $ret);
            exec('git pull --rebase', $cmdOutput, $ret);
            chdir($initCwd);
            $command = $this->getApplication()->find('parseCode');
            $input = new ArrayInput(array(
                'command' => 'parseCode',
                'path' => $initCwd.'/temp_po-'.str_replace('/', '-', $branch).'.po',
                'mask' => $input->getArgument('mask'),
                'dir' => $sourceDir,
                'kwf' => $input->getArgument('kwf')
            ));
            $returnCode = $command->run($input, $output);
            chdir($sourceDir);
        }
        exec('git checkout '.$initBranch, $cmdOutput, $ret);
        chdir($initCwd);

        // combine files
        $combinedPoFile = new \Sepia\PoParser;
        $files = scandir('.');
        foreach ($files as $path) {
            if (strpos($path, 'temp_po-') === false) continue;

            $poFile = new \Sepia\PoParser;
            $poFile->parseFile($path);
            foreach ($poFile->entries() as $entry) {
                $combinedPoFile->updateEntry($entry['msgid'][0], $entry['msgstr'][0], array(), array(), array(), true);
                if (isset($entry['msgid_plural'])) {
                    $combinedPoFile->setEntryPlural($entry['msgid'][0], $entry['msgid_plural'][0]);
                }
                if (isset($entry['msgctxt'])) {
                    $combinedPoFile->setEntryContext($entry['msgid'][0], $entry['msgctxt'][0]);
                }
            }
            unlink($path);
        }
        $combinedPoFile->writeFile($input->getArgument('path'));
    }
}
