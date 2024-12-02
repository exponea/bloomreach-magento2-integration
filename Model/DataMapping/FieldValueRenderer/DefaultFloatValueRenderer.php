<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer;

use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

/**
 * The class is responsible for rendering float value field
 */
class DefaultFloatValueRenderer implements RenderInterface
{
    /**
     * Render the value of float field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return float
     */
    public function render($entity, string $fieldCode)
    {
        return round((float) $entity->getData($fieldCode), 2);
    }
}
