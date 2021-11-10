<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Console\Command;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Api\SaveExportQueueInterface;
use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;
use Bloomreach\EngagementConnector\Model\Export\ExportFileProcessor;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\Collection;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\CollectionFactory;
use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The class is responsible for generating export files for export queue
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GenerateExportFileForExportQueue extends Command
{
    private const PAGE_SIZE = 100;

    private const MAX_RETRIES = 5;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ExportFileProcessor
     */
    private $exportFileProcessor;

    /**
     * @var EntityType
     */
    private $entityType;

    /**
     * @var SaveExportQueueInterface
     */
    private $saveExportQueue;

    /**
     * @var State
     */
    private $appState;

    /**
     * @param CollectionFactory $collectionFactory
     * @param ExportFileProcessor $exportFileProcessor
     * @param EntityType $entityType
     * @param SaveExportQueueInterface $saveExportQueue
     * @param State $appState
     * @param string|null $name
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ExportFileProcessor $exportFileProcessor,
        EntityType $entityType,
        SaveExportQueueInterface $saveExportQueue,
        State $appState,
        string $name = null
    ) {
        $this->entityType = $entityType;
        $this->collectionFactory = $collectionFactory;
        $this->exportFileProcessor = $exportFileProcessor;
        $this->saveExportQueue = $saveExportQueue;
        $this->appState = $appState;
        parent::__construct($name);
    }

    /**
     * Processes of generating files for export queue items
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws LocalizedException
     */
    public function process(InputInterface $input, OutputInterface $output): void
    {
        $entityType = (string) $input->getOption('entity_type');
        $updateExportStatus = (bool) $input->getOption('update_export_status');

        if ($entityType) {
            $this->validateEntityType($entityType);
        }

        $collection = $this->getExportQueueCollection($entityType);
        $collectionSize = $collection->getSize();

        if (!$collectionSize) {
            return;
        }

        $collection->setPageSize(self::PAGE_SIZE);
        $collection->setOrder(ExportQueueModel::ENTITY_TYPE);
        $lastPageNumber = $collection->getLastPageNumber();

        for ($currentPage = $lastPageNumber; $currentPage >= 1; $currentPage--) {
            $collection->setCurPage($currentPage);
            $this->generateFile($collection, $output, $updateExportStatus);
        }
    }

    /**
     * Validate Entity type
     *
     * @param string $entityType
     *
     * @throws LocalizedException
     */
    private function validateEntityType(string $entityType)
    {
        $entityTypes = $this->entityType->getAllTypes();

        if (!in_array($entityType, $entityTypes)) {
            throw new LocalizedException(
                __(
                    'There is no registered entity type: "%1"',
                    $entityType
                )
            );
        }
    }

    /**
     * Returns collection of the export queue items
     *
     * @param string $entityType
     *
     * @return Collection
     */
    private function getExportQueueCollection(string $entityType = ''): Collection
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            ExportQueueModel::STATUS,
            [
                'in' => [
                    ExportQueueModel::STATUS_NEW,
                    ExportQueueModel::STATUS_ERROR
                ]
            ]
        );

        $collection->addFieldToFilter(
            ExportQueueModel::RETRIES,
            [
                'lt' => self::MAX_RETRIES
            ]
        );

        if ($entityType) {
            $collection->addFieldToFilter(
                ExportQueueModel::ENTITY_TYPE,
                [
                    'eq' => $entityType
                ]
            );
        }

        return $collection;
    }

    /**
     * Generate export file
     *
     * @param Collection $collection
     * @param OutputInterface $output
     * @param bool $updateExportStatus
     *
     * @return void
     * @throws CouldNotSaveException
     */
    private function generateFile(Collection $collection, OutputInterface $output, bool $updateExportStatus): void
    {
        /** @var ExportQueueInterface $item */
        foreach ($collection as $item) {
            try {
                $output->writeln($item->getEntityType() . ': ' . $this->exportFileProcessor->process($item));
                $item->setStatus(ExportQueueModel::STATUS_COMPLETE);
            } catch (Exception $e) {
                $output->writeln($item->getEntityType() . ': ' . $e->getMessage());
                $item->setStatus(ExportQueueModel::STATUS_ERROR);
            }

            if ($updateExportStatus) {
                $item->setRetries($item->getRetries() + 1);
                $this->saveExportQueue->execute($item);
            }
        }

        $collection->clear();
    }

    /**
     * Export File Generation
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws LocalizedException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Generation of files for export has started');
        $this->appState->emulateAreaCode(
            Area::AREA_ADMINHTML,
            [
                $this,
                'process'
            ],
            [$input, $output]
        );
        $output->writeln('Generation of files for export complete');
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('bloomreach:generate-export-files')
            ->setDescription('Generate Export Files for manual upload');

        $this->addOption(
            'entity_type',
            null,
            InputOption::VALUE_OPTIONAL,
            'Entity type'
        );

        $this->addOption(
            'update_export_status',
            null,
            InputOption::VALUE_OPTIONAL,
            'Update Export Queue Status'
        );

        parent::configure();
    }
}
