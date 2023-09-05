<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Console\Command;

use Bloomreach\EngagementConnector\Model\Export\ExportProcessor;
use Bloomreach\EngagementConnector\Service\Integration\StartApiImportService;
use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for trigger export
 */
class StartExport extends Command
{
    /**
     * @var ExportProcessor
     */
    private $exportProcessor;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var StartApiImportService
     */
    private $startApiImportService;

    /**
     * @param ExportProcessor $exportProcessor
     * @param State $appState
     * @param StartApiImportService $startApiImportService
     * @param string|null $name
     */
    public function __construct(
        ExportProcessor $exportProcessor,
        State $appState,
        StartApiImportService $startApiImportService,
        string $name = null
    ) {
        parent::__construct($name);
        $this->exportProcessor = $exportProcessor;
        $this->appState = $appState;
        $this->startApiImportService = $startApiImportService;
    }

    /**
     * Export start
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Data export to Bloomreach has started');

        $importId = (string) $input->getOption('import_id');

        if ($importId) {
            try {
                $result = $this->startApiImportService->execute(
                    $importId,
                    (string) $input->getOption('csv_file_path'),
                    (bool) $input->getOption('test_connection')
                );

                $output->writeln('Status: ' . $result->getStatusCode());
                $output->writeln('Message: ' . $result->getReasonPhrase());
            } catch (Exception $e) {
                $output->writeln($e->getMessage());
                return Cli::RETURN_FAILURE;
            }
        } else {
            $this->appState->emulateAreaCode(
                Area::AREA_ADMINHTML,
                [
                    $this->exportProcessor,
                    'process'
                ],
                []
            );
        }

        $output->writeln('Data export to Bloomreach has complete');
        return Cli::RETURN_SUCCESS;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('bloomreach:export-start')
            ->setDescription('Start the Bloomreach Export');

        $this->addOption(
            'import_id',
            null,
            InputOption::VALUE_REQUIRED,
            'Import Id'
        );

        $this->addOption(
            'csv_file_path',
            null,
            InputOption::VALUE_OPTIONAL,
            'Csv file path'
        );

        $this->addOption(
            'test_connection',
            null,
            InputOption::VALUE_OPTIONAL,
            'Test connection'
        );

        parent::configure();
    }
}
