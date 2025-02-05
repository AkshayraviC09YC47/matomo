<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TestRunner\Commands;

use Piwik\Plugin\ConsoleCommand;

class TestsRunJS extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('tests:run-js');
        $this->setDescription('Run javascript tests');
        $this->addRequiredValueOption('matomo-url', null, 'Custom matomo url. Defaults to http://localhost');
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        $matomoUrl = $input->getOption('matomo-url') ?? 'http://localhost';

        $screenshotTestingDir = PIWIK_INCLUDE_PATH . "/tests/lib/screenshot-testing";
        $javascriptTestingDir = PIWIK_INCLUDE_PATH . "/tests/javascript";

        $cmdNode = "cd '$javascriptTestingDir' && NODE_PATH='$screenshotTestingDir/node_modules' node testrunnerNode.js '$matomoUrl/tests/javascript/'";

        $output->writeln('Executing command: <info>' . $cmdNode . '</info>');
        $output->writeln('');

        passthru($cmdNode, $returnCodeNode);

        $cmdPhantom = "phantomjs $javascriptTestingDir/testrunnerPhantom.js '$matomoUrl/tests/javascript/'";

        $output->writeln('');
        $output->writeln('');
        $output->writeln('Executing command: <info>' . $cmdPhantom . '</info>');
        $output->writeln('');

        passthru($cmdPhantom, $returnCodePhantom);


        return $returnCodeNode + $returnCodePhantom;
    }
}
