<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Block\Adminhtml\System\Config;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Registry;

/**
 * Get version of module for Admin module’s configuration
 */
class ModuleVersion extends Value
{
    public const MODULE_NAME = 'Bloomreach_EngagementConnector';

    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ResourceInterface $moduleResource
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ResourceInterface $moduleResource,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
        $this->moduleResource = $moduleResource;
    }

    /**
     * Inject current installed module version as the config value.
     *
     * @return void
     */
    public function afterLoad()
    {
        $version = $this->moduleResource->getDbVersion(self::MODULE_NAME);
        $this->setValue($version);
    }
}
