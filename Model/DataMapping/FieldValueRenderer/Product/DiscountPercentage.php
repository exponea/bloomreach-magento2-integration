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
 * The class is responsible for rendering the discount in percent
 */
class DiscountPercentage implements RenderInterface
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
     * @return float
     */
    public function render($entity, string $fieldCode)
    {
        $finalPrice = $this->priceDataResolver->getFinalPrice($entity);
        $originalPrice = $this->priceDataResolver->getOriginalPriceLocalCurrency($entity);

        return $originalPrice > 0 ? round((($originalPrice - $finalPrice) * 100) / $originalPrice, 2) : 0;
    }
}
