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
 * The class is responsible to render on_sale field
 */
class OnSale implements RenderInterface
{
    const TRUE = 'true';

    const FALSE = 'false';

    /**
     * @var GetDiscountService
     */
    private $getDiscountService;

    /**
     * @param GetDiscountService $getDiscountService
     */
    public function __construct(GetDiscountService $getDiscountService)
    {
        $this->getDiscountService = $getDiscountService;
    }

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
        return $this->getDiscountService->execute($entity)
            ? strtoupper(self::TRUE)
            : strtoupper(self::FALSE);
    }
}
