<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\InitialExportStatus;

use Bloomreach\EngagementConnector\Api\Data\InitialExportStatusInterface;
use Bloomreach\EngagementConnector\Api\Data\InitialExportStatusInterfaceFactory;
use Bloomreach\EngagementConnector\Api\GetInitialExportStatusInterface;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\InitialExportStatus as InitialExportStatusModel;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\ResourceModel\InitialExportStatusResourceModel;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Get Initial Export Status Entity
 */
class Get implements GetInitialExportStatusInterface
{
    /**
     * @var InitialExportStatusInterfaceFactory
     */
    private $initialExportStatusFactory;

    /**
     * @var InitialExportStatusResourceModel
     */
    private $initialExportStatusResource;

    /**
     * @param InitialExportStatusInterfaceFactory $initialExportStatusFactory
     * @param InitialExportStatusResourceModel $initialExportStatusResource
     */
    public function __construct(
        InitialExportStatusInterfaceFactory $initialExportStatusFactory,
        InitialExportStatusResourceModel $initialExportStatusResource
    ) {
        $this->initialExportStatusFactory = $initialExportStatusFactory;
        $this->initialExportStatusResource = $initialExportStatusResource;
    }

    /**
     * Get Initial Import Status
     *
     * @param string $entityType
     *
     * @return InitialExportStatusInterface
     * @throws NoSuchEntityException
     */
    public function execute(string $entityType): InitialExportStatusInterface
    {
        /** @var InitialExportStatusModel $initialExportStatusModel */
        $initialExportStatusModel = $this->initialExportStatusFactory->create();
        $this->initialExportStatusResource->load(
            $initialExportStatusModel,
            $entityType,
            InitialExportStatusModel::ENTITY_TYPE
        );

        if (!$initialExportStatusModel->getEntityId()) {
            throw new NoSuchEntityException(
                __(
                    'Entity Type with code %entity_type does not exists',
                    ['entity_type' => $entityType]
                )
            );
        }

        return $initialExportStatusModel;
    }
}
