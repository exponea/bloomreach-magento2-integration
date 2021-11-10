<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Product;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\DataMapperInterface;
use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;

/**
 * The class is responsible for rendering the value of product field for specific product type
 */
class ProductTypeRendererResolver implements RenderInterface
{
    private const DEFAULT_PRODUCT_TYPE = 'default';

    private const DEFAULT_RENDERER = 'default';

    /**
     * @var array
     */
    private $fieldValueRenderers;

    /**
     * @param array $fieldValueRenderers
     */
    public function __construct(array $fieldValueRenderers = [])
    {
        $this->fieldValueRenderers = $fieldValueRenderers;
    }

    /**
     * Render the value of product field for specific product type
     *
     * @param AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     * @throws ConfigurationMismatchException
     * @throws NoSuchEntityException
     */
    public function render($entity, string $fieldCode)
    {
        return $this->getRenderer($entity, $fieldCode)->render($entity, $fieldCode);
    }

    /**
     * Get instance of renderer for specified product type and field code
     *
     * @param AbstractModel $product
     * @param string $fieldCode
     *
     * @return RenderInterface
     * @throws ConfigurationMismatchException
     * @throws NoSuchEntityException
     */
    private function getRenderer($product, string $fieldCode): RenderInterface
    {
        $productType = isset($this->fieldValueRenderers[$product->getTypeId()][$fieldCode]) ?
            $product->getTypeId(): self::DEFAULT_PRODUCT_TYPE;

        $rendererName = isset($this->fieldValueRenderers[$productType][$fieldCode]) ?
            $fieldCode : self::DEFAULT_RENDERER;

        if (!isset($this->fieldValueRenderers[$productType][$rendererName])) {
            throw new NoSuchEntityException(
                __(
                    'There is no such field value renderer for %2 product type for interface %3',
                    $productType,
                    $rendererName,
                    DataMapperInterface::class
                )
            );
        }

        $renderer = $this->fieldValueRenderers[$productType][$rendererName];

        if (!($renderer instanceof RenderInterface)) {
            throw new ConfigurationMismatchException(
                __(
                    'Field value renderer "%1" must implement interface %2',
                    get_class($renderer),
                    RenderInterface::class
                )
            );
        }

        return $renderer;
    }
}
