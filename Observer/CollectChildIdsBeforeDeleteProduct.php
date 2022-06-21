<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Observer;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;
use Bloomreach\EngagementConnector\Model\Product\ChildIdsDataProvider;
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
     * @param ChildIdsDataProvider $childIdsDataProvider
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        ChildIdsDataProvider $childIdsDataProvider,
        ConfigProvider $configProvider
    ) {
        $this->childIdsDataProvider = $childIdsDataProvider;
        $this->configProvider = $configProvider;
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
        if ($this->configProvider->isEnabled()
            && $this->configProvider->getCatalogId()
            && $this->configProvider->getCatalogVariantsId()
        ) {
            $event = $observer->getEvent();
            /** @var $product Product */
            $product = $event->getProduct();

            $this->childIdsDataProvider->collectIds($product);
        }
    }
}
