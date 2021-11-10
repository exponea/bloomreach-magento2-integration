<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Entity;

use Bloomreach\EngagementConnector\Model\Export\Entity\AddMultipleToExport;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

/**
 * This class is responsible for adding entity id to the export
 */
class AddToExport
{
    /**
     * @var AddMultipleToExport
     */
    private $addMultipleToExport;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param AddMultipleToExport $addMultipleToExport
     * @param LoggerInterface $logger
     */
    public function __construct(
        AddMultipleToExport $addMultipleToExport,
        LoggerInterface $logger
    ) {
        $this->addMultipleToExport = $addMultipleToExport;
        $this->logger = $logger;
    }

    /**
     * Adds entity id to the export
     *
     * @param string $entityType
     * @param int $exportEntityId
     *
     * @return void
     */
    public function execute(string $entityType, int $exportEntityId): void
    {
        try {
            $this->addMultipleToExport->execute($entityType, [$exportEntityId]);
        } catch (Exception $e) {
            $this->logger->error(
                __(
                    'An error occurred while adding entity to the export. Error: %1',
                    $e->getMessage()
                )
            );
        }
    }
}
