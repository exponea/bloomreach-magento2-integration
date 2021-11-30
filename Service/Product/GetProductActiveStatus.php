<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Product;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

/**
 * The class is responsible for generate product_active status for variant
 */
class GetProductActiveStatus
{
    private const TRUE = 'true';

    private const FALSE = 'false';

    /**
     * @var Configurable
     */
    private $configurableType;

    /**
     * @var Type
     */
    private $bundleType;

    /**
     * @var Grouped
     */
    private $groupedType;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var array
     */
    private $resultCache = [];

    /**
     * @param Configurable $configurableType
     * @param Type $bundleType
     * @param Grouped $groupedType
     * @param CollectionFactory $collectionFactory
     * @param SerializerInterface $jsonSerializer
     */
    public function __construct(
        Configurable $configurableType,
        Type $bundleType,
        Grouped $groupedType,
        CollectionFactory $collectionFactory,
        SerializerInterface $jsonSerializer
    ) {
        $this->configurableType = $configurableType;
        $this->bundleType = $bundleType;
        $this->groupedType = $groupedType;
        $this->collectionFactory = $collectionFactory;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Generate product active status
     *
     * @param ProductInterface $product
     *
     * @return string
     */
    public function execute(ProductInterface $product): string
    {
        if ((int) $product->getStatus() === Status::STATUS_DISABLED) {
            return $this->getBoolValue();
        } elseif ((int) $product->getVisibility() !== Visibility::VISIBILITY_NOT_VISIBLE) {
            return $this->getBoolValue((int) $product->getStatus() === Status::STATUS_ENABLED);
        }

        $parentIds = $this->getParentIds((int) $product->getId());

        if (!count($parentIds)) {
            return $this->getBoolValue();
        }

        return $this->getActiveStatus($parentIds);
    }

    /**
     * Convert boolean value to string
     *
     * @param bool $value
     *
     * @return string
     */
    private function getBoolValue(bool $value = false): string
    {
        return strtoupper($value ? self::TRUE : self::FALSE);
    }

    /**
     * Retrieve parent products ids
     *
     * @param int $childProductId
     *
     * @return array
     */
    private function getParentIds(int $childProductId): array
    {
        $configurableIds = $this->configurableType->getParentIdsByChild($childProductId);
        $bundleIds = $this->bundleType->getParentIdsByChild($childProductId);
        $groupedIds = $this->groupedType->getParentIdsByChild($childProductId);

        return array_unique(array_merge($configurableIds, $bundleIds, $groupedIds));
    }

    /**
     * Returns active status
     *
     * @param array $parentIds
     *
     * @return string
     */
    private function getActiveStatus(array $parentIds): string
    {
        $hashedKey = $this->getHashedKey($parentIds);

        if (!array_key_exists($hashedKey, $this->resultCache)) {
            //Clear cache
            $this->clearCache();

            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addAttributeToFilter(ProductInterface::STATUS, Status::STATUS_ENABLED);
            $collection->addFieldToFilter('entity_id', ['in' => $parentIds]);
            $collection->setPageSize(1);
            $this->resultCache[$hashedKey] = $this->getBoolValue((bool) $collection->getFirstItem()->getId());
        }

        return $this->resultCache[$hashedKey];
    }

    /**
     * Get hashed key based on the array
     *
     * @param array $parentIds
     *
     * @return string
     */
    private function getHashedKey(array $parentIds): string
    {
        return sha1($this->jsonSerializer->serialize($parentIds));
    }

    /**
     * Clear cache
     *
     * @return void
     */
    private function clearCache(): void
    {
        if (count($this->resultCache) > 100) {
            $this->resultCache = [];
        }
    }
}
