<?php

declare(strict_types=1);

/**
 * This file is part of the KDN OZG package.
 *
 * @author    Gert Hammes <info@gerthammes.de>
 * @copyright 2019 Gert Hammes
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Entity\Maturity;
use App\Import\DataProvider\CsvDataProvider;
use App\Import\SolutionImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 */
class SolutionImportCommand extends Command
{

    protected static $defaultName = 'app:import:solution';

    /**
     * @var SolutionImporter
     */
    private $importer;

    /**
     * @required
     * @param SolutionImporter $importer
     */
    public function injectImporter(SolutionImporter $importer): void
    {
        $this->importer = $importer;
    }

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Import solutions from a csv file in a given directory')
            ->addArgument(
                'directory',
                InputArgument::REQUIRED,
                'the import directory'
            )
            ->addArgument(
                'form-server-id',
                InputArgument::REQUIRED,
                'the form server id'
            )
            ->addArgument(
                'maturity-id',
                InputArgument::OPTIONAL,
                'the maturity server id',
                Maturity::DEFAULT_ID
            )
            ->setHelp('Imports the solutions from a CSV file in the given directory;'
                . PHP_EOL . 'If you want to get more detailed information, use the --verbose option.');
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $directory = $input->getArgument('directory');
        $formServerId = (int)$input->getArgument('form-server-id');
        $maturityId = (int)$input->getArgument('maturity-id');
        $startTime = microtime(true);
        $this->importer->setOutput($output);
        $this->importer->setMaturityId($maturityId);
        $this->importer->setFormServerById($formServerId);
        $dataProvider = new CsvDataProvider($directory);
        $importedRowCount = $this->importer->run($dataProvider);
        $durationSeconds = round(microtime(true) - $startTime, 3);
        $io->note(sprintf('Finished import process. %s records were imported in %s seconds', $importedRowCount, $durationSeconds));
    }
}