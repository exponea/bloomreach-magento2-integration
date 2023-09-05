<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Setup\Patch\Data;

use Bloomreach\EngagementConnector\Api\Data\InitialExportStatusInterface;
use Bloomreach\EngagementConnector\Api\Data\InitialExportStatusInterfaceFactory;
use Bloomreach\EngagementConnector\Api\SaveInitialExportStatusInterface;
use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;
use Bloomreach\EngagementConnector\Model\Export\Entity\CollectionFactory;
use Bloomreach\EngagementConnector\Model\Export\Entity\ProductCollection;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource;
use Bloomreach\EngagementConnector\System\ConfigPathGetter;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * The class is responsible for creating initial export status entity
 */
class CreateInitialExportStatus implements DataPatchInterface
{
    /**
     * @var EntityType
     */
    private $entityType;

    /**
     * @var ConfigPathGetter
     */
    private $configPathGetter;

    /**
     * @var ConfigResource
     */
    private $configResource;

    /**
     * @var SaveInitialExportStatusInterface
     */
    private $saveInitialExportStatus;

    /**
     * @var InitialExportStatusInterfaceFactory
     */
    private $initialExportStatusFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param EntityType $entityType
     * @param ConfigPathGetter $configPathGetter
     * @param ConfigResource $configResource
     * @param SaveInitialExportStatusInterface $saveInitialExportStatus
     * @param InitialExportStatusInterfaceFactory $initialExportStatusFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        EntityType $entityType,
        ConfigPathGetter $configPathGetter,
        ConfigResource $configResource,
        SaveInitialExportStatusInterface $saveInitialExportStatus,
        InitialExportStatusInterfaceFactory $initialExportStatusFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->entityType = $entityType;
        $this->configPathGetter = $configPathGetter;
        $this->configResource = $configResource;
        $this->saveInitialExportStatus = $saveInitialExportStatus;
        $this->initialExportStatusFactory = $initialExportStatusFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get Patch Dependencies
     *
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [
            MigrateConfigurations::class
        ];
    }

    /**
     * Creates initial export status entities
     *
     * @return $this
     */
    public function apply()
    {
        foreach ($this->entityType->getAllTypes() as $entityType) {
            if (!$this->isImportIdExists($entityType)) {
                continue;
            }

            $this->createInitialExportStatusEntity($entityType);

        }
        return $this;
    }

    /**
     * Get Patch Aliases
     *
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Create Initial Export Status Entity
     *
     * @param string $entityType
     *
     * @return void
     */
    private function createInitialExportStatusEntity(string $entityType): void
    {
        $collection = $this->getCollection($entityType);
        $size = $collection->getSize();

        if (!$size) {
            return;
        }

        /** @var InitialExportStatusInterface $initialExportStatus */
        $initialExportStatus = $this->initialExportStatusFactory->create();
        $initialExportStatus->setStatus(StatusSource::SUCCESS);
        $initialExportStatus->setTotalItems($size);
        $initialExportStatus->setTotalExported($size);
        $initialExportStatus->setEntityType($entityType);
        $initialExportStatus->setFinishedAt(date('Y-m-d H:i:s'));
        $this->saveInitialExportStatus->execute($initialExportStatus);
    }

    /**
     * Checks if import id exists
     *
     * @param string $entityType
     *
     * @return bool
     * @throws LocalizedException
     */
    private function isImportIdExists(string $entityType): bool
    {
        $connection = $this->configResource->getConnection();
        $select = $connection->select()->from(
            $this->configResource->getMainTable()
        )->where(
            'path = ?',
            $this->configPathGetter->get($entityType, ConfigProvider::IMPORT_ID_TYPE)
        );

        return trim((string) $connection->fetchOne($select)) !== '';
    }

    /**
     * Create Collection for Entity type
     *
     * @param string $entityType
     *
     * @return AbstractDb
     */
    private function getCollection(string $entityType)
    {
        $collection = $this->collectionFactory->create($entityType);

        if ($collection instanceof ProductCollection) {
            $collection->setIsDefaultMode(true);
        }

        return $collection;
    }
}
