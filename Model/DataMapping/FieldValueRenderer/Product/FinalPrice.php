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

/**
 * The class is responsible to get product final price
 */
class FinalPrice implements RenderInterface
{
    /**
     * Render the boolean product value
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return float
     */
    public function render($entity, string $fieldCode)
    {
        return round((float) $entity->getFinalPrice(), 2);
    }
}
