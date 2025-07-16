<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Product;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Service\BooleanConverter;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

/**
 * The class is responsible to render the product status field
 */
class Status implements RenderInterface
{
    /**
     * @var BooleanConverter
     */
    private $booleanConverter;

    /**
     * @param BooleanConverter $booleanConverter
     */
    public function __construct(BooleanConverter $booleanConverter)
    {
        $this->booleanConverter = $booleanConverter;
    }

    /**
     * Render the product status value
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        return $this->booleanConverter->toString(
            (int) $entity->getData($fieldCode) === ProductStatus::STATUS_ENABLED
        );
    }
}
