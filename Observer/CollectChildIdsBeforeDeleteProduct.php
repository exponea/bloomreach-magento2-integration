<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Observer;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\ProductVariantsType;
use Bloomreach\EngagementConnector\Model\Export\Condition\IsRealTimeUpdateAllowed;
use Bloomreach\EngagementConnector\Model\Product\ChildIdsDataProvider;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * The class is responsible for collecting product child ids before delete
 */
class CollectChildIdsBeforeDeleteProduct implements ObserverInterface
{
    /**
     * @var ChildIdsDataProvider
     */
    private $childIdsDataProvider;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var IsRealTimeUpdateAllowed
     */
    private $isRealTimeUpdateAllowed;

    /**
     * @param ChildIdsDataProvider $childIdsDataProvider
     * @param ConfigProvider $configProvider
     * @param IsRealTimeUpdateAllowed $isRealTimeUpdateAllowed
     */
    public function __construct(
        ChildIdsDataProvider $childIdsDataProvider,
        ConfigProvider $configProvider,
        IsRealTimeUpdateAllowed $isRealTimeUpdateAllowed
    ) {
        $this->childIdsDataProvider = $childIdsDataProvider;
        $this->configProvider = $configProvider;
        $this->isRealTimeUpdateAllowed = $isRealTimeUpdateAllowed;
    }

    /**
     * Collect product child ids before delete product
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        if (!$this->configProvider->isEnabled()
            || !$this->isRealTimeUpdateAllowed->execute(ProductVariantsType::ENTITY_TYPE)
        ) {
            return;
        }

        $event = $observer->getEvent();
        /** @var $product Product */
        $product = $event->getProduct();

        $this->childIdsDataProvider->collectIds($product);
    }
}
