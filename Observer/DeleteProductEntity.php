<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Observer;

use Bloomreach\EngagementConnector\Service\Export\DeleteProductEntity as DeleteProductEntityService;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * The class is responsible for delete product entity on the Bloomreach side
 */
class DeleteProductEntity implements ObserverInterface
{
    /**
     * @var DeleteProductEntityService
     */
    private $deleteProductEntity;

    /**
     * @param DeleteProductEntityService $deleteProductEntity
     */
    public function __construct(DeleteProductEntityService $deleteProductEntity)
    {
        $this->deleteProductEntity = $deleteProductEntity;
    }

    /**
     * Delete product entity on the Bloomreach side
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

        $this->deleteProductEntity->execute($product);
    }
}
