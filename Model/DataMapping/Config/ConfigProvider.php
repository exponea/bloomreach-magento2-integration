<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Provide system configuration value
 */
class ConfigProvider
{
    /**
     * Enable Integration system configuration value XPATH
     */
    public const XPATH_IS_ENABLE = 'bloomreach_engagement/general/enable';

    /**
     * API Target system configuration value XPATH
     */
    public const XPATH_API_TARGET = 'bloomreach_engagement/general/api_target';

    /**
     * API Key ID system configuration value XPATH
     */
    public const XPATH_API_KEY_ID = 'bloomreach_engagement/general/api_key_id';

    /**
     * API Secret system configuration value XPATH
     */
    public const XPATH_API_SECRET = 'bloomreach_engagement/general/api_secret';

    /**
     * Token (Project ID) system configuration value XPATH
     */
    public const XPATH_PROJECT_TOKEN_ID = 'bloomreach_engagement/general/project_token_id';

    /**
     * Catalog Import Id system configuration value XPATH
     */
    public const XPATH_CATALOG_IMPORT_ID = 'bloomreach_engagement/general/catalog_import_id';

    /**
     * Variants import Id system configuration value XPATH
     */
    public const XPATH_VARIANTS_IMPORT_ID = 'bloomreach_engagement/general/variants_import_id';

    /**
     * Customer import id system configuration value XPATH
     */
    public const XPATH_CUSTOMER_IMPORT_ID = 'bloomreach_engagement/general/customer_import_id';

    /**
     * Orders import id system configuration value XPATH
     */
    public const XPATH_ORDER_IMPORT_ID = 'bloomreach_engagement/general/order_import_id';

    /**
     * Orders (line item) import id system configuration value XPATH
     */
    public const XPATH_ORDER_IMPORTLINE_ITEM_ID = 'bloomreach_engagement/general/order_importline_item_id';

    /**
     * Enable Real Time Data Synchronization system configuration value XPATH
     */
    public const XPATH_ENABLE_DATA_SYNC = 'bloomreach_engagement/general/enable_realtime_data_synchronization';

    /**
     * Enable Exponea JS SDK system configuration value XPATH
     */
    public const XPATH_ENABLE_JS_SDK = 'bloomreach_engagement/frontend_tracking/enable_js_sdk';

    /**
     * Enable Exponea JS SDK system configuration value XPATH
     */
    public const XPATH_ENABLE_PURCHASE_TRACKING = 'bloomreach_engagement/frontend_tracking/enable_purchase';

    /**
     * Enable Exponea JS SDK system configuration value XPATH
     */
    public const XPATH_ENABLE_PRODUCT_VIEW_TRACKING = 'bloomreach_engagement/frontend_tracking/enable_product_view';

    /**
     * Catalog Id system configuration value XPATH
     */
    public const XPATH_CATALOG_ID = 'bloomreach_engagement/general/catalog_id';

    /**
     * Catalog Variants Id system configuration value XPATH
     */
    public const XPATH_CATALOG_VARIANTS_ID = 'bloomreach_engagement/general/variants_id';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get is enabled system value
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return bool
     */
    public function isEnabled(string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_IS_ENABLE, $scopeType, $scopeCode);
    }

    /**
     * Get Api Target system value
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getApiTarget(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(self::XPATH_API_TARGET, $scopeType, $scopeCode);
    }

    /**
     * Get api key id system value
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getApiKeyId(string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null): string
    {
        return (string) $this->scopeConfig->getValue(self::XPATH_API_KEY_ID, $scopeType, $scopeCode);
    }

    /**
     * Get api secret system value
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getApiSecret(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(self::XPATH_API_SECRET, $scopeType, $scopeCode);
    }

    /**
     * Get project token id system value
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getProjectTokenId($scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null): string
    {
        return (string) $this->scopeConfig->getValue(self::XPATH_PROJECT_TOKEN_ID, $scopeType, $scopeCode);
    }

    /**
     * Get catalog import id system value
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getCatalogImportId(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(self::XPATH_CATALOG_IMPORT_ID, $scopeType, $scopeCode);
    }

    /**
     * Get variants import id system value
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string|null
     */
    public function getVariantsImportId(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(self::XPATH_VARIANTS_IMPORT_ID, $scopeType, $scopeCode);
    }

    /**
     * Get customer import id system value
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getCustomerImportId(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(self::XPATH_CUSTOMER_IMPORT_ID, $scopeType, $scopeCode);
    }

    /**
     * Get order import id system value
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getOrderImportId($scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null): string
    {
        return (string) $this->scopeConfig->getValue(self::XPATH_ORDER_IMPORT_ID, $scopeType, $scopeCode);
    }

    /**
     * Get order import line item id system value
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getOrderImportLineItemId(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(
            self::XPATH_ORDER_IMPORTLINE_ITEM_ID,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get is enable data sync system value
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return bool
     */
    public function isEnableDataSync(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::XPATH_ENABLE_DATA_SYNC, $scopeType, $scopeCode);
    }

    /**
     * Get Catalog ID
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getCatalogId(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return $this->scopeConfig->getValue(self::XPATH_CATALOG_ID, $scopeType, $scopeCode);
    }

    /**
     * Checks whether is enabled purchase tracking
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return bool
     */
    public function isPurchaseTrackingEnabled(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::XPATH_ENABLE_PURCHASE_TRACKING, $scopeType, $scopeCode);
    }

    /**
     * Checks whether product view tracking is enabled
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return bool
     */
    public function isProductViewTrackingEnabled(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::XPATH_ENABLE_PRODUCT_VIEW_TRACKING, $scopeType, $scopeCode);
    }

    /**
     * Checks whether is enabled Js SDK system value
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return bool
     */
    public function isJsSdkEnabled(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::XPATH_ENABLE_JS_SDK, $scopeType, $scopeCode);
    }

    /**
     * Get Catalog Variants ID
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getCatalogVariantsId(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return $this->scopeConfig->getValue(self::XPATH_CATALOG_VARIANTS_ID, $scopeType, $scopeCode);
    }
}
