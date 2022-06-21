<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Observer;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;
use Bloomreach\EngagementConnector\Service\Export\DeleteProductEntity as DeleteProductEntityService;
use Bloomreach\EngagementConnector\Service\Export\UpdateProductVariantsStatus;
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
     * @var UpdateProductVariantsStatus
     */
    private $updateProductVariantsStatus;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @param DeleteProductEntityService $deleteProductEntity
     * @param UpdateProductVariantsStatus $updateProductVariantsStatus
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        DeleteProductEntityService $deleteProductEntity,
        UpdateProductVariantsStatus $updateProductVariantsStatus,
        ConfigProvider $configProvider
    ) {
        $this->deleteProductEntity = $deleteProductEntity;
        $this->updateProductVariantsStatus = $updateProductVariantsStatus;
        $this->configProvider = $configProvider;
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
        if ($this->configProvider->isEnabled()
            && $this->configProvider->getCatalogId()
            && $this->configProvider->getCatalogVariantsId()
        ) {
            $event = $observer->getEvent();
            /** @var $product Product */
            $product = $event->getProduct();

            $this->deleteProductEntity->execute($product);
            $this->updateProductVariantsStatus->execute($product);
        }
    }
}
