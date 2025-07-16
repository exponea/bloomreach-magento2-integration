<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\ProductVariants;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Service\BooleanConverter;
use Bloomreach\EngagementConnector\Service\Product\GetProductActiveStatus;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

/**
 * The class is responsible for rendering the product_active field
 */
class ProductActive implements RenderInterface
{
    /**
     * @var GetProductActiveStatus
     */
    private $getProductActiveStatus;

    /**
     * @var BooleanConverter
     */
    private $booleanConverter;

    /**
     * @param GetProductActiveStatus $getProductActiveStatus
     * @param BooleanConverter $booleanConverter
     */
    public function __construct(
        GetProductActiveStatus $getProductActiveStatus,
        BooleanConverter $booleanConverter
    ) {
        $this->getProductActiveStatus = $getProductActiveStatus;
        $this->booleanConverter = $booleanConverter;
    }

    /**
     * Render the product_active field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode): string
    {
        return $this->booleanConverter->toString($this->getProductActiveStatus->execute($entity));
    }
}
