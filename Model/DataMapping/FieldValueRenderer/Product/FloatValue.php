<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Product;

use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

/**
 * The class is responsible for rendering the value of product field
 */
class FloatValue extends DefaultRenderer
{
    /**
     * Render the value of product field and convert to float
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return float
     */
    public function render($entity, string $fieldCode)
    {
        return (float) parent::render($entity, $fieldCode);
    }
}
