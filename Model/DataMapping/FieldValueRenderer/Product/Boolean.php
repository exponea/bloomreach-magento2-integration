<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Product;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

/**
 * The class is responsible to render boolean field
 */
class Boolean implements RenderInterface
{
    const TRUE = 'true';

    const FALSE = 'false';

    /**
     * Render the boolean product value
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        if ((int) $entity->getData($fieldCode) === Status::STATUS_ENABLED) {
            return strtoupper(self::TRUE);
        }

        return strtoupper(self::FALSE);
    }
}
