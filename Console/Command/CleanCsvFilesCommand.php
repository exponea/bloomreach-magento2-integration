<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Console\Command;

use Bloomreach\EngagementConnector\Service\Cron\CleanCsvFilesService;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Clean old csv files
 */
class CleanCsvFilesCommand extends Command
{
    /**
     * @var CleanCsvFilesService
     */
    private $cleanCsvFilesService;

    /**
     * @param CleanCsvFilesService $cleanCsvFilesService
     */
    public function __construct(CleanCsvFilesService $cleanCsvFilesService)
    {
        $this->cleanCsvFilesService = $cleanCsvFilesService;

        parent::__construct();
    }

    /**
     * Clean old csv files
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Start of cleaning old csv files');

        $this->cleanCsvFilesService->execute();

        $output->writeln('Finish of cleaning old csv files');
        return Cli::RETURN_SUCCESS;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('bloomreach:clean-csv-files')
            ->setDescription('Clean old csv files');

        parent::configure();
    }
}
