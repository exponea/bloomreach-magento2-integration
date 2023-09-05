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
use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * The class is responsible for obtaining an InitialExportStatusEntity
 */
class ItemGetter
{
    /**
     * @var GetInitialExportStatusInterface
     */
    private $getInitialExportStatus;

    /**
     * @var InitialExportStatusInterfaceFactory
     */
    private $initialExportStatusFactory;

    /**
     * @var ItemUpdater
     */
    private $itemUpdater;

    /**
     * @var EntityType
     */
    private $entityType;

    /**
     * @param InitialExportStatusInterfaceFactory $initialExportStatusFactory
     * @param GetInitialExportStatusInterface $getInitialExportStatus
     * @param ItemUpdater $itemUpdater
     * @param EntityType $entityType
     */
    public function __construct(
        InitialExportStatusInterfaceFactory $initialExportStatusFactory,
        GetInitialExportStatusInterface $getInitialExportStatus,
        ItemUpdater $itemUpdater,
        EntityType $entityType
    ) {
        $this->initialExportStatusFactory = $initialExportStatusFactory;
        $this->getInitialExportStatus = $getInitialExportStatus;
        $this->itemUpdater = $itemUpdater;
        $this->entityType = $entityType;
    }

    /**
     * Returns InitialExportStatusEntity
     *
     * @param string $entityType
     *
     * @return InitialExportStatusInterface
     * @throws NoSuchEntityException
     */
    public function execute(string $entityType): InitialExportStatusInterface
    {
        if (!in_array($entityType, $this->entityType->getAllTypes())) {
            throw new NoSuchEntityException(
                __(
                    'Entity Type with code %entity_type does not exists',
                    ['entity_type' => $entityType]
                )
            );
        }

        return $this->itemUpdater->execute($this->getInitialExportStatusEntity($entityType));
    }

    /**
     * Get initial export status entity
     *
     * @param string $entityType
     *
     * @return InitialExportStatusInterface
     */
    private function getInitialExportStatusEntity(string $entityType): InitialExportStatusInterface
    {
        try {
            return $this->getInitialExportStatus->execute($entityType);
        } catch (NoSuchEntityException $e) {
            /** @var InitialExportStatusInterface $initialExportStatus */
            $initialExportStatus = $this->initialExportStatusFactory->create();
            $initialExportStatus->setEntityType($entityType);

            return $initialExportStatus;
        }
    }
}
