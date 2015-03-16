<?php
namespace Kwf\Trl;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kwf\Trl\Parse\ParsePhpForTrl;
use Kwf\Trl\Parse\ParseJsForTrl;
use Kwf\Trl\Parse\ParseAll;
use Kwf\Trl\Utils\PoFileGenerator;
use Kwf\Trl\Utils\TrlElementsExtractor;

class ParseCodeCommand extends Command
{
    protected function configure()
    {
        $this->setName('parseCode')
            ->setDescription('Parse code for trl and trlKwf function calls')
            ->addArgument('dir', InputArgument::OPTIONAL, 'Path to source directory', null)
            ->addOption('poFile', 'p', InputOption::VALUE_REQUIRED, 'Path for po-file', 'trl.po')
            ->addOption('mask', 'm', InputOption::VALUE_REQUIRED, 'Mask to parse for. This can be trl or trlKwf', 'trlKwf')
            ->addOption('kwfpath', 'k', InputOption::VALUE_REQUIRED, 'Path to kwf po-file (only if parsing package)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $kwfTrlElements = array();
        $kwfPoFilePath = $input->getOption('kwfpath');
        if ($kwfPoFilePath) {
            $output->writeln('<info>Reading kwf-po file</info>');
            $kwfPoFile = new \Sepia\PoParser;
            $kwfPoFile->parseFile($kwfPoFilePath);
            $trlElementsExtractor = new TrlElementsExtractor($kwfPoFile);
            $kwfTrlElements = $trlElementsExtractor->extractTrlElements();
        }

        $initDir = getcwd();
        $directory = $input->getArgument('dir');
        $output->writeln('<info>Changing into directory</info>');
        chdir($directory);
        exec('git diff', $diff, $ret);
        exec('git diff --cached', $diffCached, $ret);
        if ($diff || $diffCached) {
            $output->writeln('<error>Uncommited changes</error>');
            exit;
        }
        chdir($initDir);

        $trlElements = array();
        $errors = array();
        $git = new \Kwf_Util_Git($directory);
        $git->fetch();
        $initBranch = $git->getActiveBranch();
        $output->writeln('<info>Iterating over branches matching "^[3-9]+.[0-9]+$" and >=3.9</info>');
        foreach ($git->getBranches('-r') as $branch) {
            if (strpos($branch, 'origin/') === false) continue;
            $splited = explode('/', $branch);
            $isVersionNumber = preg_match('/^[0-9]+.[0-9]+$/i', $splited[1]);
            if (!$isVersionNumber && $splited[1] != 'master' && $splited[1] != 'production') continue;

            if ($isVersionNumber && version_compare($splited[1], '3.9', '<')) continue;

            $output->writeln("<info>Checking out branch: $branch</info>");
            $git->checkout($branch);
            // parse package
            $output->writeln('Parsing source directory...');
            $parser = new ParseAll($directory, $output);
            $trlElements = array_merge($parser->parseDirectoryForTrl(), $trlElements);
            $errors = array_merge($parser->getErrors(), $errors);
        }
        $git->checkout($initBranch);

        // generate po file
        $output->writeln('Generate Po-File...');
        $poFileGenerator = new PoFileGenerator($trlElements, $kwfTrlElements);
        $poFile = $poFileGenerator->generatePoFileObject();
        $poFile->writeFile($input->getOption('poFile'));

        $output->writeln('Trl Errors:');
        foreach ($trlElements as $trlElement) {
            if (isset($trlElement['error_short']) && $trlElement['error_short']) {
                var_dump($trlElement);
            }
        }

        if (count($errors)) {
            $output->writeln('Php Parse-Errors:');
            foreach ($errors as $error) {
                var_dump($error);
            }
        }
    }
}
