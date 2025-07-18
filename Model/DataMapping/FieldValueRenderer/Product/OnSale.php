<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Product;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Service\BooleanConverter;
use Bloomreach\EngagementConnector\Service\Product\GetDiscountService;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

/**
 * The class is responsible to render on_sale field
 */
class OnSale implements RenderInterface
{
    /**
     * @deprecated
     */
    public const TRUE = 'true';

    /**
     * @Deprecated
     */
    public const FALSE = 'false';

    /**
     * @var GetDiscountService
     */
    private $getDiscountService;

    /**
     * @var BooleanConverter
     */
    private $booleanConverter;

    /**
     * @param GetDiscountService $getDiscountService
     * @param BooleanConverter $booleanConverter
     */
    public function __construct(
        GetDiscountService $getDiscountService,
        BooleanConverter $booleanConverter
    ) {
        $this->getDiscountService = $getDiscountService;
        $this->booleanConverter = $booleanConverter;
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
        return $this->booleanConverter->toString($this->getDiscountService->execute($entity) > 0);
    }
}
