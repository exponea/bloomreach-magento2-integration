<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Console\Command;

use Bloomreach\EngagementConnector\Service\Cron\CleanExportQueueService;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Clean export queue data from DB
 */
class CleanExportQueueCommand extends Command
{
    /**
     * @var CleanExportQueueService
     */
    private $cleanExportQueueService;

    /**
     * @param CleanExportQueueService $cleanExportQueueService
     */
    public function __construct(CleanExportQueueService $cleanExportQueueService)
    {
        $this->cleanExportQueueService = $cleanExportQueueService;

        parent::__construct();
    }

    /**
     * Clean export queue data from DB
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Start of cleaning the export queue data');

        $this->cleanExportQueueService->execute();

        $output->writeln('Finish of cleaning the export queue data');

        return Cli::RETURN_SUCCESS;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('bloomreach:clean-export-queue')
            ->setDescription('Clean export queue data from DB');

        parent::configure();
    }
}
