<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Product;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Service\Product\GetDiscountService;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

/**
 * Render the discount of product price
 */
class Discount implements RenderInterface
{
    /**
     * @var GetDiscountService
     */
    private $getDiscountService;

    /**
     * Discount constructor.
     * @param GetDiscountService $getDiscountService
     */
    public function __construct(GetDiscountService $getDiscountService)
    {
        $this->getDiscountService = $getDiscountService;
    }

    /**
     * Render the discount of product price
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        return number_format($this->getDiscountService->execute($entity), 2, '.', '');
    }
}
