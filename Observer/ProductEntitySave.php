<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Observer;

use Bloomreach\EngagementConnector\Service\Export\PrepareProductDataService;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 *  Get product entity after save
 */
class ProductEntitySave implements ObserverInterface
{
    /**
     * @var PrepareProductDataService
     */
    private $prepareProductDataService;

    /**
     * @param PrepareProductDataService $prepareProductDataService
     */
    public function __construct(PrepareProductDataService $prepareProductDataService)
    {
        $this->prepareProductDataService = $prepareProductDataService;
    }

    /**
     * Get product entity after save
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $event = $observer->getEvent();
        /** @var $product Product */
        $product = $event->getProduct();

        $this->prepareProductDataService->execute($product);
    }
}
