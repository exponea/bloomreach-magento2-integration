<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Product;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Service\Product\GetParentProductByChildId;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;

/**
 * The class is responsible for rendering the url field
 */
class Url implements RenderInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var GetParentProductByChildId
     */
    private $getParentProductByChildId;

    /**
     * @param GetParentProductByChildId $getParentProductByChildId
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        GetParentProductByChildId $getParentProductByChildId,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->getParentProductByChildId = $getParentProductByChildId;
    }

    /**
     * Render the url product value
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        /** @var Product $entity */
        if ($entity->getStoreId() === 0) {
            // This workaround is required to correctly render the frontend URL for the current active store
            $defaultStoreId = $this->storeManager->getDefaultStoreView()->getId();
            $entity->setStoreId($defaultStoreId);
        }

        if (!$entity->isVisibleInSiteVisibility() && $entity->getTypeId() === Type::TYPE_SIMPLE) {
            $parentProduct = $this->getParentProductByChildId->execute((int) $entity->getId(), $entity->getStoreId());

            if (!$parentProduct) {
                return '';
            }

            try {
                if ($parentProduct->getTypeId() === Configurable::TYPE_CODE) {
                    $attributes = $parentProduct->getTypeInstance()->getConfigurableAttributesAsArray($parentProduct);

                    return $this->removeBaseRoot($parentProduct->getUrlModel()->getUrl($parentProduct)
                        . $this->getSimpleOptions($attributes, $entity));
                }

                return $this->removeBaseRoot($parentProduct->getProductUrl());
            } catch (NoSuchEntityException $exception) {
                return '';
            }
        }

        return $this->removeBaseRoot($entity->getProductUrl());
    }

    /**
     * Remove redundant base root folder
     *
     * @param string $url
     *
     * @return string
     */
    private function removeBaseRoot(string $url): string
    {
        if (strpos($url, '/magento/') !== false) {
            $url = str_replace('/magento/', '/', $url);
        }

        return $url;
    }

    /**
     * Get simple hash options
     *
     * @param array $attributes
     * @param Product $entity
     *
     * @return string
     */
    private function getSimpleOptions(array $attributes, Product $entity): string
    {
        $options = [];

        foreach ($attributes as $attribute) {
            $id = $attribute[AttributeInterface::ATTRIBUTE_ID];
            $value = $entity->getData($attribute[AttributeInterface::ATTRIBUTE_CODE]);
            $options[$id] = $value;
        }

        $options = http_build_query($options);
        return $options ? '#' . $options : '';
    }
}
