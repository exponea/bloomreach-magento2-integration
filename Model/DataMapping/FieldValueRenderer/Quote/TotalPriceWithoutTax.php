<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Quote;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Service\Quote\GetQuoteTotals;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

/**
 * The class is responsible for rendering the total_price_without_tax field
 */
class TotalPriceWithoutTax implements RenderInterface
{
    /**
     * @var GetQuoteTotals
     */
    private $getQuoteTotals;

    /**
     * @param GetQuoteTotals $getQuoteTotals
     */
    public function __construct(GetQuoteTotals $getQuoteTotals)
    {
        $this->getQuoteTotals = $getQuoteTotals;
    }

    /**
     * Render the value of quote field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return float
     */
    public function render($entity, string $fieldCode)
    {
        $quoteTotals = $this->getQuoteTotals->execute($entity);
        $totalWithoutTax = 0;

        if ($quoteTotals) {
            $totalWithoutTax = $quoteTotals->getBaseGrandTotal() - $quoteTotals->getBaseTaxAmount();
        }

        return $totalWithoutTax ? (float) round((float) $entity->getBaseGrandTotal(), 2) : 0.0;
    }
}
