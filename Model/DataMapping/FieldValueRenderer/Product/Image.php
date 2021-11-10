<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Product;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * The class is responsible to get image url
 */
class Image implements RenderInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManagerInterface
     */
    public function __construct(StoreManagerInterface $storeManagerInterface)
    {
        $this->storeManager = $storeManagerInterface;
    }

    /**
     * Render the image url of product
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode): string
    {
        if (!$entity->getData($fieldCode)) {
            return '';
        }

        $path = 'catalog/product' . $entity->getData($fieldCode);

        return $this->storeManager->getStore((int) $entity->getStoreId())
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $path;
    }
}
