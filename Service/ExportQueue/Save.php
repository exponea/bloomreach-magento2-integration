<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\ExportQueue;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Api\SaveExportQueueInterface;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue as ExportQueueResourceModel;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * The class is responsible for saving ExportQueue entity
 */
class Save implements SaveExportQueueInterface
{
    /**
     * @var ExportQueueResourceModel
     */
    private $exportQueueResourceModel;

    /**
     * @param ExportQueueResourceModel $exportQueueResourceModel
     */
    public function __construct(ExportQueueResourceModel $exportQueueResourceModel)
    {
        $this->exportQueueResourceModel = $exportQueueResourceModel;
    }

    /**
     * Saves export entity
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return ExportQueueInterface
     * @throws CouldNotSaveException
     */
    public function execute(ExportQueueInterface $exportQueue): ExportQueueInterface
    {
        try {
            $this->exportQueueResourceModel->save($exportQueue);
        } catch (Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save Export Entity. Entity Type: %1. Error Message: %2',
                    $exportQueue->getEntityType(),
                    $e->getMessage()
                )
            );
        }
        
        return $exportQueue;
    }
}
