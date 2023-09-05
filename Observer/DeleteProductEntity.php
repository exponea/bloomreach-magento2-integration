<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Observer;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\ProductVariantsType;
use Bloomreach\EngagementConnector\Model\Export\Condition\IsRealTimeUpdateAllowed;
use Bloomreach\EngagementConnector\Service\Export\DeleteProductEntity as DeleteProductEntityService;
use Bloomreach\EngagementConnector\Service\Export\UpdateProductVariantsStatus;
use Bloomreach\EngagementConnector\System\ConfigProvider;
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
     * @var IsRealTimeUpdateAllowed
     */
    private $isRealTimeUpdateAllowed;

    /**
     * @param DeleteProductEntityService $deleteProductEntity
     * @param UpdateProductVariantsStatus $updateProductVariantsStatus
     * @param ConfigProvider $configProvider
     * @param IsRealTimeUpdateAllowed $isRealTimeUpdateAllowed
     */
    public function __construct(
        DeleteProductEntityService $deleteProductEntity,
        UpdateProductVariantsStatus $updateProductVariantsStatus,
        ConfigProvider $configProvider,
        IsRealTimeUpdateAllowed $isRealTimeUpdateAllowed
    ) {
        $this->deleteProductEntity = $deleteProductEntity;
        $this->updateProductVariantsStatus = $updateProductVariantsStatus;
        $this->configProvider = $configProvider;
        $this->isRealTimeUpdateAllowed = $isRealTimeUpdateAllowed;
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
        if (!$this->configProvider->isEnabled()) {
            return;
        }

        $event = $observer->getEvent();
        /** @var $product Product */
        $product = $event->getProduct();

        $this->deleteProductEntity->execute($product);

        if ($this->isRealTimeUpdateAllowed->execute(ProductVariantsType::ENTITY_TYPE)) {
            $this->updateProductVariantsStatus->execute($product);
        }
    }
}
