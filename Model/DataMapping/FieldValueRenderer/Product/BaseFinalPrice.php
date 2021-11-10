<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Product;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Model\Product\PriceDataResolver;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

/**
 * The class is responsible for rendering the base final price
 */
class BaseFinalPrice implements RenderInterface
{
    /**
     * @var PriceDataResolver
     */
    private $priceDataResolver;

    /**
     * @param PriceDataResolver $priceDataResolver
     */
    public function __construct(PriceDataResolver $priceDataResolver)
    {
        $this->priceDataResolver = $priceDataResolver;
    }

    /**
     * Render the value of entity type field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        return $this->priceDataResolver->getBaseFinalPrice($entity);
    }
}
