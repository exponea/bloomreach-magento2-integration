<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Product;

use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

/**
 * Get discount value
 */
class GetDiscountService
{
    /**
     * Get product discount
     *
     * @param AbstractSimpleObject|AbstractModel $product
     *
     * @return float
     */
    public function execute($product): float
    {
        $specialPrice = $product->getSpecialPrice();
        $specialPriceFromDate = $product->getSpecialFromDate();
        $specialPriceToDate = $product->getSpecialToDate() ?? date('Y-m-d', strtotime('+1 day'));
        $today = time();
        $price = $product->getPrice();

        if ($specialPrice) {
            if ($today >= strtotime($specialPriceFromDate)
                && $today <= strtotime($specialPriceToDate)
                || $today >= strtotime($specialPriceFromDate)
                && $specialPriceToDate === null
            ) {
                return round($price - $specialPrice, 4);
            }
        }

        return 0;
    }
}
