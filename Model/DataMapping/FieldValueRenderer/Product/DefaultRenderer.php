<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Product;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Service\EavAttribute\GetAttributeValue;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

/**
 * The class is responsible for rendering the value of product field
 */
class DefaultRenderer implements RenderInterface
{
    /**
     * @var GetAttributeValue
     */
    private $getAttributeValue;

    /**
     * @param GetAttributeValue $getAttributeValue
     */
    public function __construct(GetAttributeValue $getAttributeValue)
    {
        $this->getAttributeValue = $getAttributeValue;
    }

    /**
     * Render the value of product field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        return (string) $this->getAttributeValue->execute(
            $entity->getData($fieldCode),
            $fieldCode,
            ProductAttributeInterface::ENTITY_TYPE_CODE
        );
    }
}
