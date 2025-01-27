<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\ViewItem;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Service\Product\GetDefaultStoreId;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\StoreManagerInterface;

/**
 * This class is responsible for rendering product name for default store view
 */
class Name implements RenderInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository
    ) {
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
    }

    /**
     * Get default store view product name
     *
     * @param Product $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        if ($this->storeManager->isSingleStoreMode()) {
            return $entity->getName();
        }

        try {
            $defaultStoreId = (int) $this->storeManager->getDefaultStoreView()->getId();

            return (int) $entity->getStoreId() === $defaultStoreId
                ? $entity->getName()
                : $this->productRepository->getById($entity->getId(), false, $defaultStoreId)->getName();
        } catch (Exception $e) {
            return $entity->getName();
        }
    }
}
