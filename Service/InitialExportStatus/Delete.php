<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\InitialExportStatus;

use Bloomreach\EngagementConnector\Api\DeleteInitialExportStatusInterface;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\InitialExportStatus as InitialExportStatusModel;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\ResourceModel\InitialExportStatusResourceModel;
use Exception;
use Magento\Framework\Exception\CouldNotDeleteException;
use Psr\Log\LoggerInterface;

/**
 * Deletes Initial Export Status Model
 */
class Delete implements DeleteInitialExportStatusInterface
{
    /**
     * @var InitialExportStatusResourceModel
     */
    private $initialExportStatusResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param InitialExportStatusResourceModel $initialExportStatusResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        InitialExportStatusResourceModel $initialExportStatusResource,
        LoggerInterface $logger
    ) {

        $this->initialExportStatusResource = $initialExportStatusResource;
        $this->logger = $logger;
    }

    /**
     * Deletes Initial Export Status
     *
     * @param string $entityType
     *
     * @return void
     * @throws CouldNotDeleteException
     */
    public function execute(string $entityType): void
    {
        try {
            $connection = $this->initialExportStatusResource->getConnection();
            $connection->delete(
                $this->initialExportStatusResource->getMainTable(),
                [InitialExportStatusModel::ENTITY_TYPE . ' = ?' => $entityType]
            );
        } catch (Exception $e) {
            $this->logger->error(
                __(
                    'An error occurred while deleting Initial Export Status. Error: %error',
                    ['error' => $e->getMessage()]
                )
            );
            throw new CouldNotDeleteException(
                __(
                    'Could not delete initial export entity with entity type: %entity_type',
                    [
                        'entity_type' => $entityType
                    ]
                )
            );
        }
    }
}
