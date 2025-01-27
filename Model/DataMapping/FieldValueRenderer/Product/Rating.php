<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Product;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Review\Model\Review;

/**
 * The class is responsible to get rating value of product
 */
class Rating implements RenderInterface
{
    /**
     * @var Review
     */
    private $review;

    /**
     * @param Review $review
     */
    public function __construct(
        Review $review
    ) {
        $this->review = $review;
    }

    /**
     * Render the boolean product value
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return float
     */
    public function render($entity, string $fieldCode)
    {
        $rating = 0;
        $storeId = (int) $entity->getStoreId();
        $this->review->getEntitySummary($entity, $storeId);

        if ($fieldCode === 'rating') {
            $rating = round($entity->getRatingSummary()->getRatingSummary() / 20, 2);
        }

        if ($fieldCode === 'ratings_count') {
            $rating = $entity->getRatingSummary()->getReviewsCount();
        }

        return (float) $rating;
    }
}
