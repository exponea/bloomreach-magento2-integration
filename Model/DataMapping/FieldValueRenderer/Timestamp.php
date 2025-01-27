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
 * The class is responsible for rendering the timestamp field
 */
class Timestamp implements RenderInterface
{
    /**
     * Render the timestamp product value
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return int
     */
    public function render($entity, string $fieldCode)
    {
        return (int) strtotime((string) $entity->getData($fieldCode));
    }
}
