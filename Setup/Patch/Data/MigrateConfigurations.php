<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Setup\Patch\Data;

use Bloomreach\EngagementConnector\System\ConfigProvider;
use Exception;
use Magento\Config\Model\ResourceModel\Config\Data as ConfigDataResource;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Config\Model\ResourceModel\Config\Data\Collection;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Migrates data from old configurations to new ones
 */
class MigrateConfigurations implements DataPatchInterface
{
    private const OLD_XPATH_PRODUCTS_IMPORT_ID = 'bloomreach_engagement/general/catalog_import_id';

    private const OLD_XPATH_VARIANTS_IMPORT_ID = 'bloomreach_engagement/general/variants_import_id';

    private const OLD_XPATH_CUSTOMER_IMPORT_ID = 'bloomreach_engagement/general/customer_import_id';

    private const OLD_XPATH_PURCHASE_IMPORT_ID = 'bloomreach_engagement/general/order_import_id';

    private const OLD_XPATH_PURCHASE_ITEMS_IMPORT_ID = 'bloomreach_engagement/general/order_importline_item_id';

    private const OLD_XPATH_CATALOG_PRODUCTS_ID = 'bloomreach_engagement/general/catalog_id';

    private const OLD_XPATH_CATALOG_VARIANTS_ID = 'bloomreach_engagement/general/variants_id';

    private const CONFIG_MAPPING = [
        self::OLD_XPATH_PRODUCTS_IMPORT_ID => ConfigProvider::XPATH_PRODUCTS_IMPORT_ID,
        self::OLD_XPATH_VARIANTS_IMPORT_ID => ConfigProvider::XPATH_VARIANTS_IMPORT_ID,
        self::OLD_XPATH_CUSTOMER_IMPORT_ID => ConfigProvider::XPATH_CUSTOMER_IMPORT_ID,
        self::OLD_XPATH_PURCHASE_IMPORT_ID => ConfigProvider::XPATH_PURCHASE_IMPORT_ID,
        self::OLD_XPATH_PURCHASE_ITEMS_IMPORT_ID => ConfigProvider::XPATH_PURCHASE_ITEMS_IMPORT_ID,
        self::OLD_XPATH_CATALOG_PRODUCTS_ID => ConfigProvider::XPATH_CATALOG_PRODUCTS_ID,
        self::OLD_XPATH_CATALOG_VARIANTS_ID => ConfigProvider::XPATH_CATALOG_VARIANTS_ID
    ];

    private const DEPENDS_CONFIG = [
        ConfigProvider::XPATH_PRODUCTS_FEED_ENABLED => [
            self::OLD_XPATH_PRODUCTS_IMPORT_ID
        ],
        ConfigProvider::XPATH_VARIANTS_FEED_ENABLED => [
            self::OLD_XPATH_VARIANTS_IMPORT_ID
        ],
        ConfigProvider::XPATH_CUSTOMERS_FEED_ENABLED => [
            self::OLD_XPATH_CUSTOMER_IMPORT_ID
        ],
        ConfigProvider::XPATH_PURCHASE_FEED_ENABLED => [
            self::OLD_XPATH_PURCHASE_IMPORT_ID
        ],
        ConfigProvider::XPATH_PURCHASE_ITEMS_FEED_ENABLED => [
            self::OLD_XPATH_PURCHASE_ITEMS_IMPORT_ID
        ],
        ConfigProvider::XPATH_PRODUCTS_REAL_TIME_UPDATES_ENABLED => [
            self::OLD_XPATH_PRODUCTS_IMPORT_ID,
            self::OLD_XPATH_CATALOG_PRODUCTS_ID
        ],
        ConfigProvider::XPATH_VARIANTS_REAL_TIME_UPDATES_ENABLED => [
            self::OLD_XPATH_VARIANTS_IMPORT_ID,
            self::OLD_XPATH_CATALOG_VARIANTS_ID
        ],
        ConfigProvider::XPATH_CUSTOMERS_REAL_TIME_UPDATES_ENABLED => [
            self::OLD_XPATH_CUSTOMER_IMPORT_ID
        ],
        ConfigProvider::XPATH_PURCHASE_REAL_TIME_UPDATES_ENABLED => [
            self::OLD_XPATH_PURCHASE_IMPORT_ID
        ],
        ConfigProvider::XPATH_PURCHASE_ITEMS_REAL_TIME_UPDATES_ENABLED => [
            self::OLD_XPATH_PURCHASE_ITEMS_IMPORT_ID
        ]
    ];

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ConfigDataResource
     */
    private $configDataResource;

    /**
     * @param CollectionFactory $collectionFactory
     * @param WriterInterface $configWriter
     * @param ConfigDataResource $configDataResource
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        WriterInterface $configWriter,
        ConfigDataResource $configDataResource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->configWriter = $configWriter;
        $this->configDataResource = $configDataResource;
    }

    /**
     * Migrates Configurations
     *
     * @return void
     * @throws Exception
     */
    public function apply()
    {
        $configs = $this->getConfigs();

        if (!$configs) {
            return;
        }

        $this->migrateConfigs($configs);
        $this->createNewConfigs($configs);
        $this->deleteOldConfigs($configs);
    }

    /**
     * Get Patch Dependencies
     *
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get Patch Aliases
     *
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Returns Configs Array
     *
     * @return Value[]
     */
    private function getConfigs(): array
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('path', ['in' => array_keys(self::CONFIG_MAPPING)]);

        if (!$collection->getSize()) {
            return [];
        }

        $result = [];

        foreach ($collection->getItems() as $config) {
            $result[$config->getPath()] = $config;
        }

        return $result;
    }

    /**
     * Migrate Configs
     *
     * @param Value[] $configsData
     *
     * @return void
     */
    private function migrateConfigs(array $configsData): void
    {
        foreach (self::CONFIG_MAPPING as $oldConfigPath => $newConfigPath) {
            $oldConfig = $configsData[$oldConfigPath] ?? '';

            if (!$oldConfig || trim((string) $oldConfig->getValue()) === '') {
                continue;
            }

            $this->configWriter->save($newConfigPath, $oldConfig->getValue());
        }
    }

    /**
     * Create new configs
     *
     * @param Value[] $configsData
     *
     * @return void
     */
    private function createNewConfigs(array $configsData): void
    {
        foreach (self::DEPENDS_CONFIG as $configPath => $configsPool) {
            if ($this->isValid($configsPool, $configsData)) {
                $this->configWriter->save($configPath, 1);
            }
        }
    }

    /**
     * Checks if all needed configs have value
     *
     * @param string[] $configsPool
     * @param Value[] $configsData
     *
     * @return bool
     */
    private function isValid(array $configsPool, array $configsData): bool
    {
        foreach ($configsPool as $path) {
            $value = $configsData[$path] ?? '';

            if (!$value || trim((string) $value->getValue()) === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Delete old configs
     *
     * @param Value[] $configsData
     *
     * @return void
     * @throws Exception
     */
    private function deleteOldConfigs(array $configsData): void
    {
        foreach ($configsData as $config) {
            $this->configDataResource->delete($config);
        }
    }
}
