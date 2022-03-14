<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Setup\Service;

use Exception;
use Magento\Config\Model\ResourceModel\Config\Data as ConfigDataResource;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory as ConfigDataCollectionFactory;

/**
 * Clear system configurations
 */
class ClearConfig
{
    /**
     * @var ConfigDataResource
     */
    private $configDataResource;

    /**
     * @var ConfigDataCollectionFactory
     */
    private $configDataCollectionFactory;

    /**
     * @param ConfigDataResource $configDataResource
     * @param ConfigDataCollectionFactory $configDataCollectionFactory
     */
    public function __construct(
        ConfigDataResource $configDataResource,
        ConfigDataCollectionFactory $configDataCollectionFactory
    ) {
        $this->configDataResource = $configDataResource;
        $this->configDataCollectionFactory = $configDataCollectionFactory;
    }

    /**
     * Removing configurations
     *
     * @return void
     * @throws Exception
     */
    public function execute(): void
    {
        $configCollection = $this->configDataCollectionFactory->create()
            ->addPathFilter('bloomreach_engagement');

        foreach ($configCollection as $config) {
            $this->configDataResource->delete($config);
        }
    }
}
