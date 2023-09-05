<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */

declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Entity;

use Bloomreach\EngagementConnector\Model\ResourceModel\EavAttribute as EavAttributeResource;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Helper;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\EntityFactory as EavEntityFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Collection model for Product
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductCollection extends Collection
{
    /**
     * @var int
     */
    private $lastItemId;

    /**
     * @var bool
     */
    private $isDefaultMode;

    /**
     * @var EavAttributeResource
     */
    private $eavAttributeResource;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param Config $eavConfig
     * @param ResourceConnection $resource
     * @param EavEntityFactory $eavEntityFactory
     * @param Helper $resourceHelper
     * @param UniversalFactory $universalFactory
     * @param StoreManagerInterface $storeManager
     * @param Manager $moduleManager
     * @param State $catalogProductFlatState
     * @param ScopeConfigInterface $scopeConfig
     * @param OptionFactory $productOptionFactory
     * @param Url $catalogUrl
     * @param TimezoneInterface $localeDate
     * @param Session $customerSession
     * @param DateTime $dateTime
     * @param GroupManagementInterface $groupManagement
     * @param EavAttributeResource $eavAttributeResource
     * @param ProductMetadataInterface $productMetadata
     * @param bool $isDefaultMode
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Config $eavConfig,
        ResourceConnection $resource,
        EavEntityFactory $eavEntityFactory,
        Helper $resourceHelper,
        UniversalFactory $universalFactory,
        StoreManagerInterface $storeManager,
        Manager $moduleManager,
        State $catalogProductFlatState,
        ScopeConfigInterface $scopeConfig,
        OptionFactory $productOptionFactory,
        Url $catalogUrl,
        TimezoneInterface $localeDate,
        Session $customerSession,
        DateTime $dateTime,
        GroupManagementInterface $groupManagement,
        EavAttributeResource $eavAttributeResource,
        ProductMetadataInterface $productMetadata,
        bool $isDefaultMode = false
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement
        );
        $this->eavAttributeResource = $eavAttributeResource;
        $this->productMetadata = $productMetadata;
        $this->isDefaultMode = $isDefaultMode;
    }

    /**
     * Returns collection size
     *
     * @return int
     */
    public function getSize()
    {
        if ($this->isDefaultMode()) {
            return parent::getSize();
        }

        return $this->getImprovedSize();
    }

    /**
     * Set Is Default Mode
     *
     * @param bool $isDefaultMode
     *
     * @return void
     */
    public function setIsDefaultMode(bool $isDefaultMode): void
    {
        $this->isDefaultMode = $isDefaultMode;
    }

    /**
     * Set greater than item ID filter
     *
     * @param int $itemId
     *
     * @return void
     */
    public function addGtThenItemIdFilter(int $itemId): void
    {
        if ($this->isDefaultMode()) {
            $this->addFieldToFilter($this->getPrimaryKey(), ['gt' => $itemId]);
        } else {
            $this->setLastItemId($itemId);
        }
    }

    /**
     * Get Last Loaded Item ID
     *
     * @return int
     */
    public function getLastLoadedItemId(): int
    {
        return (int) parent::getLastItem()->getData($this->getPrimaryKey());
    }

    /**
     * Adds products filter
     *
     * @return $this
     */
    protected function _renderFilters()
    {
        $this->addVisibilityFilter();
        parent::_renderFilters();

        return $this;
    }

    /**
     * Add visibility filter
     *
     * First load ids of visible products then filter collection by ids
     *
     * Should be used instead of default filter on the Large catalogs: over 300 000 products
     *
     * @return void
     */
    private function addImprovedVisibilityFilter()
    {
        $ids = $this->getVisibleProductIds();

        if ($ids) {
            $this->addFieldToFilter($this->getPrimaryKey(), ['in' => $ids]);
        }
    }

    /**
     * Returns list of visible product ids
     *
     * [entity_id] - for Magento Community
     *
     * [row_id] - for Magento Enterprise
     *
     * @return array
     */
    private function getVisibleProductIds(): array
    {
        $connection = $this->getResource()->getConnection();
        $select = $connection->select()->reset();
        $select->from(
            $connection->getTableName('catalog_product_entity_int'),
            [$this->getPrimaryKey()]
        )->where(
            'value != ?',
            Visibility::VISIBILITY_NOT_VISIBLE
        )->where(
            'attribute_id = ?',
            $this->eavAttributeResource->getAttributeId(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                ProductInterface::VISIBILITY
            )
        )->where(
            $this->getPrimaryKey() . ' > ?',
            $this->getLastItemId(),
        )->where(
            'store_id = ?',
            0
        )->limit($this->getPageSize())->order($this->getPrimaryKey());

        $result = $connection->fetchCol($select);

        return $result ?: [];
    }

    /**
     * Calculate collection size
     *
     * @return int
     */
    private function getImprovedSize(): int
    {
        $connection = $this->getResource()->getConnection();
        $select = $connection->select()->reset();
        $select->from(
            $connection->getTableName('catalog_product_entity_int'),
            ['COUNT(*)']
        )->where(
            'value != ?',
            Visibility::VISIBILITY_NOT_VISIBLE
        )->where(
            'attribute_id = ?',
            $this->eavAttributeResource->getAttributeId(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                ProductInterface::VISIBILITY
            )
        )->where(
            'store_id = ?',
            0
        );

        return (int) $connection->fetchOne($select);
    }

    /**
     * Adds visibility filter
     *
     * @return void
     */
    private function addVisibilityFilter()
    {
        if ($this->isDefaultMode()) {
            $this->addAttributeToFilter(
                ProductInterface::VISIBILITY,
                ['neq' => Visibility::VISIBILITY_NOT_VISIBLE]
            );

            return;
        }

        $this->addImprovedVisibilityFilter();
    }

    /**
     * Returns primary key depending on Magento Edition
     *
     * @return string
     */
    private function getPrimaryKey(): string
    {
        return $this->isEnterpriseEdition() ? 'row_id' : 'entity_id';
    }

    /**
     * Checks if Magento edition is Enterprise
     *
     * @return bool
     */
    private function isEnterpriseEdition(): bool
    {
        return $this->productMetadata->getEdition() !== ProductMetadata::EDITION_NAME;
    }

    /**
     * Get Is Default Mode
     *
     * @return bool
     */
    private function isDefaultMode(): bool
    {
        return (bool) $this->isDefaultMode;
    }

    /**
     * Get last item ID
     *
     * @return int
     */
    private function getLastItemId(): int
    {
        return (int) $this->lastItemId;
    }

    /**
     * Set last item ID
     *
     * @param int $lastItemId
     *
     * @return void
     */
    private function setLastItemId(int $lastItemId): void
    {
        $this->lastItemId = $lastItemId;
    }
}
