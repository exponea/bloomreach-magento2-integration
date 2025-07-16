<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Config\Backend;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\DefaultType;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\ProductVariantsType;
use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;
use Bloomreach\EngagementConnector\Service\Integration\GetCatalog;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

/**
 * The class is validate 'Real Time Updates' field
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidateCatalog extends Value
{
    /**
     * @var GetCatalog
     */
    private $getCatalog;

    /**
     * @var EntityType
     */
    private $entityType;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param GetCatalog $getCatalog
     * @param EntityType $entityType
     * @param ConfigProvider $configProvider
     * @param LoggerInterface $logger
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        GetCatalog $getCatalog,
        EntityType $entityType,
        ConfigProvider $configProvider,
        LoggerInterface $logger,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->configProvider = $configProvider;
        $this->getCatalog = $getCatalog;
        $this->entityType = $entityType;
        $this->logger = $logger;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Validate 'Real Time Updates' field
     *
     * @return void
     *
     * @throws LocalizedException
     */
    public function validateBeforeSave()
    {
        if ($this->getValue() === '1') {
            switch ($this->getPath()) {
                case ConfigProvider::XPATH_PRODUCTS_REAL_TIME_UPDATES_ENABLED:
                    $catalogId = $this->configProvider->getCatalogProductsId();
                    $catalogName = $this->entityType->getEntityName(DefaultType::ENTITY_TYPE);
                    break;
                case ConfigProvider::XPATH_VARIANTS_REAL_TIME_UPDATES_ENABLED:
                    $catalogId = $this->configProvider->getCatalogVariantsId();
                    $catalogName = $this->entityType->getEntityName(ProductVariantsType::ENTITY_TYPE);
                    break;
                default:
                    $catalogId = '';
                    $catalogName = '';
                    break;
            }

            if (!$catalogId) {
                throw new LocalizedException(
                    __(
                        'Failed to enable Real Time Updates for %catalog_name.'
                        . ' Catalog Id is empty. Please, configure %catalog_name initial import and try again',
                        ['catalog_name' => $catalogName]
                    )
                );
            }

            try {
                $this->getCatalog->execute($catalogId);
            } catch (NoSuchEntityException $e) {
                throw new LocalizedException(
                    __(
                        'Failed to enable Real Time Updates for %catalog_name. Original error message: %error_message',
                        ['error_message' => $e->getMessage(), 'catalog_name' => $catalogName]
                    )
                );
            } catch (LocalizedException $e) {
                $this->logger->error(
                    __(
                        'Failed to enable Real Time Updates for %catalog_name. Original error message: %error_message',
                        ['error_message' => $e->getMessage(), 'catalog_name' => $catalogName]
                    )
                );
                throw new LocalizedException(
                    __(
                        'Failed to enable Real Time Updates for %catalog_name. See log for a detailed error message',
                        ['catalog_name' => $catalogName]
                    )
                );
            }
        }

        parent::beforeSave();
    }
}
