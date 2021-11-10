<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer;

use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

/**
 * Render the value of entity field
 */
interface RenderInterface
{
    /**
     * Render the value of entity field
     *
     * Must return simple type: string, float, int, array
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string|int|float|array
     */
    public function render($entity, string $fieldCode);
}
