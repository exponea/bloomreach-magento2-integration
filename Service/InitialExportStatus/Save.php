<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\InitialExportStatus;

use Bloomreach\EngagementConnector\Api\Data\InitialExportStatusInterface;
use Bloomreach\EngagementConnector\Api\SaveInitialExportStatusInterface;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\ResourceModel\InitialExportStatusResourceModel;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

/**
 * Saves Initial Export Status Model
 */
class Save implements SaveInitialExportStatusInterface
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
     * Saves Initial Export Status
     *
     * @param InitialExportStatusInterface $initialExportStatus
     *
     * @return InitialExportStatusInterface
     * @throws CouldNotSaveException
     */
    public function execute(InitialExportStatusInterface $initialExportStatus): InitialExportStatusInterface
    {
        try {
            $this->initialExportStatusResource->save($initialExportStatus);
        } catch (Exception $e) {
            $this->logger->error(
                __(
                    'An error occurred while saving Initial Export Status. Error: %error',
                    ['error' => $e->getMessage()]
                )
            );

            throw new CouldNotSaveException(
                __('Could not save Initial Export Status.'),
                $e
            );
        }

        return $initialExportStatus;
    }
}
