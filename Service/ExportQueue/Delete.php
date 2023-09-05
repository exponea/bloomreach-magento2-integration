<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\ExportQueue;

use Bloomreach\EngagementConnector\Api\DeleteExportQueueInterface;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\ExportQueueModelFactory;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue as ExportQueueResourceModel;
use Exception;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * The class is responsible for deleting ExportQueue entity by Id
 */
class Delete implements DeleteExportQueueInterface
{
    /**
     * @var ExportQueueResourceModel
     */
    private $exportQueueResourceModel;

    /**
     * @var ExportQueueModelFactory
     */
    private $exportQueueModelFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ExportQueueResourceModel $exportQueueResourceModel
     * @param ExportQueueModelFactory $exportQueueModelFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ExportQueueResourceModel $exportQueueResourceModel,
        ExportQueueModelFactory $exportQueueModelFactory,
        LoggerInterface $logger
    ) {
        $this->exportQueueResourceModel = $exportQueueResourceModel;
        $this->exportQueueModelFactory = $exportQueueModelFactory;
        $this->logger = $logger;
    }

    /**
     * Delete export queue item
     *
     * @param int $entityId
     *
     * @return void
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function execute(int $entityId): void
    {
        $exportQueueModel = $this->exportQueueModelFactory->create();
        $this->exportQueueResourceModel->load($exportQueueModel, $entityId);

        if (!$exportQueueModel->getData(ExportQueueModel::ENTITY_ID)) {
            throw new NoSuchEntityException(
                __(
                    'Export Queue Item with ID: `%entity_id` does not exists',
                    [
                        'entity_id' => $entityId
                    ]
                )
            );
        }

        try {
            $this->exportQueueResourceModel->delete($exportQueueModel);
        } catch (Exception $e) {
            $this->logger->error(
                __(
                    'Could not delete Export Entity Item. Original message: %message',
                    [
                        'message' => $e->getMessage()
                    ]
                )
            );

            throw new CouldNotDeleteException(
                __(
                    'Could not Delete Export Entity Item. Error Message: %1',
                    $e->getMessage()
                )
            );
        }
    }
}
