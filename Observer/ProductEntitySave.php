<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Observer;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\ProductVariantsType;
use Bloomreach\EngagementConnector\Model\Export\Condition\IsRealTimeUpdateAllowed;
use Bloomreach\EngagementConnector\Service\Export\PrepareProductDataService;
use Bloomreach\EngagementConnector\Service\Export\UpdateProductVariantsStatus;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Catalog\Api\Data\ProductInterface;
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
     * @param PrepareProductDataService $prepareProductDataService
     * @param UpdateProductVariantsStatus $updateProductVariantsStatus
     * @param ConfigProvider $configProvider
     * @param IsRealTimeUpdateAllowed $isRealTimeUpdateAllowed
     */
    public function __construct(
        PrepareProductDataService $prepareProductDataService,
        UpdateProductVariantsStatus $updateProductVariantsStatus,
        ConfigProvider $configProvider,
        IsRealTimeUpdateAllowed $isRealTimeUpdateAllowed
    ) {
        $this->prepareProductDataService = $prepareProductDataService;
        $this->updateProductVariantsStatus = $updateProductVariantsStatus;
        $this->configProvider = $configProvider;
        $this->isRealTimeUpdateAllowed = $isRealTimeUpdateAllowed;
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
        if (!$this->configProvider->isEnabled()) {
            return;
        }

        $event = $observer->getEvent();
        /** @var $product Product */
        $product = $event->getProduct();

        $this->prepareProductDataService->execute($product);

        if ($product->getStatus() !== $product->getOrigData(ProductInterface::STATUS)
            && $this->isRealTimeUpdateAllowed->execute(ProductVariantsType::ENTITY_TYPE)
        ) {
            $this->updateProductVariantsStatus->execute($product);
        }
    }
}
