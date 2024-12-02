<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\System;

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
     * Catalog Products Feed Import ID system configuration value XPATH
     */
    public const XPATH_PRODUCTS_IMPORT_ID = 'bloomreach_engagement/catalog_product_feed/import_id';

    /**
     * Catalog Product Variants Feed import ID system configuration value XPATH
     */
    public const XPATH_VARIANTS_IMPORT_ID = 'bloomreach_engagement/catalog_product_variants_feed/import_id';

    /**
     * Customers Feed import ID system configuration value XPATH
     */
    public const XPATH_CUSTOMER_IMPORT_ID = 'bloomreach_engagement/customer_feed/import_id';

    /**
     * Purchase Feed import ID system configuration value XPATH
     */
    public const XPATH_PURCHASE_IMPORT_ID = 'bloomreach_engagement/purchase_feed/import_id';

    /**
     * Purchase Items import ID system configuration value XPATH
     */
    public const XPATH_PURCHASE_ITEMS_IMPORT_ID = 'bloomreach_engagement/purchase_item_feed/import_id';

    /**
     * Enable Exponea JS SDK system configuration value XPATH
     */
    public const XPATH_ENABLE_JS_SDK = 'bloomreach_engagement/frontend_tracking/enable_js_sdk';

    /**
     * Enable Exponea Data Layer system configuration value XPATH
     */
    public const XPATH_ENABLE_DATALAYER = 'bloomreach_engagement/frontend_tracking/enable_datalayer';

    /**
     * Enable Exponea JS SDK system configuration value XPATH
     */
    public const XPATH_ENABLE_PURCHASE_TRACKING = 'bloomreach_engagement/frontend_tracking/enable_purchase';

    /**
     * Enable Exponea JS SDK system configuration value XPATH
     */
    public const XPATH_ENABLE_PRODUCT_VIEW_TRACKING = 'bloomreach_engagement/frontend_tracking/enable_product_view';

    /**
     * Enable Exponea JS SDK system configuration value XPATH
     */
    public const XPATH_ENABLE_CART_UPDATE_TRACKING = 'bloomreach_engagement/frontend_tracking/enable_cart_update';

    /**
     * Catalog ID system configuration value XPATH
     */
    public const XPATH_CATALOG_PRODUCTS_ID = 'bloomreach_engagement/catalog_product_feed/catalog_id';

    /**
     * Catalog Variants ID system configuration value XPATH
     */
    public const XPATH_CATALOG_VARIANTS_ID = 'bloomreach_engagement/catalog_product_variants_feed/catalog_id';

    /**
     * Enable Debug Mode system configuration value XPATH
     */
    public const XPATH_ENABLE_DEBUG_MODE = 'bloomreach_engagement/general/debug_mode';

    /**
     * Request timeout system configuration value XPATH
     */
    public const XPATH_REQUEST_TIMEOUT = 'bloomreach_engagement/general/request_timeout';

    /**
     * Base Wait Time system configuration value XPATH
     */
    public const XPATH_RETRY_BASE_WAIT_TIME = 'bloomreach_engagement/general/retry_base_wait_time';

    /**
     * Max Wait Time system configuration value XPATH
     */
    public const XPATH_RETRY_MAX_WAIT_TIME = 'bloomreach_engagement/general/retry_max_wait_time';

    /**
     * Enables Catalog Products Feed value XPATH
     */
    public const XPATH_PRODUCTS_FEED_ENABLED = 'bloomreach_engagement/catalog_product_feed/enabled';

    /**
     * Enables Catalog Product Variants Feed value XPATH
     */
    public const XPATH_VARIANTS_FEED_ENABLED = 'bloomreach_engagement/catalog_product_variants_feed/enabled';

    /**
     * Enables Customers Feed value XPATH
     */
    public const XPATH_CUSTOMERS_FEED_ENABLED = 'bloomreach_engagement/customer_feed/enabled';

    /**
     * Enables Purchase Feed value XPATH
     */
    public const XPATH_PURCHASE_FEED_ENABLED = 'bloomreach_engagement/purchase_feed/enabled';

    /**
     * Enables Purchase Items Feed value XPATH
     */
    public const XPATH_PURCHASE_ITEMS_FEED_ENABLED = 'bloomreach_engagement/purchase_item_feed/enabled';

    /**
     * Enables Catalog Products Feed Real Time Updates value XPATH
     */
    public const XPATH_PRODUCTS_REAL_TIME_UPDATES_ENABLED =
        'bloomreach_engagement/catalog_product_feed/real_time_updates';

    /**
     * Enables Catalog Product Variants Feed Real Time Updates value XPATH
     */
    public const XPATH_VARIANTS_REAL_TIME_UPDATES_ENABLED =
        'bloomreach_engagement/catalog_product_variants_feed/real_time_updates';

    /**
     * Enables Customers Feed Real Time Updates value XPATH
     */
    public const XPATH_CUSTOMERS_REAL_TIME_UPDATES_ENABLED = 'bloomreach_engagement/customer_feed/real_time_updates';

    /**
     * Enables Purchase Feed Real Time Updates value XPATH
     */
    public const XPATH_PURCHASE_REAL_TIME_UPDATES_ENABLED = 'bloomreach_engagement/purchase_feed/real_time_updates';

    /**
     * Enables Purchase Items Feed Real Time Updates value XPATH
     */
    public const XPATH_PURCHASE_ITEMS_REAL_TIME_UPDATES_ENABLED =
        'bloomreach_engagement/purchase_item_feed/real_time_updates';

    /**
     * Registered mapping Customer ID field value XPATH
     */
    public const XPATH_REGISTERED_MAPPING_CUSTOMER_ID_FIELD = 'bloomreach_engagement/registered_mapping/customer_id';

    /**
     * Registered mapping. Email field value XPATH
     */
    public const XPATH_REGISTERED_MAPPING_EMAIL_FIELD = 'bloomreach_engagement/registered_mapping/email';

    /**
     * Notification enabled XPATH
     */
    public const XPATH_NOTIFICATION_ENABLED = 'bloomreach_engagement/notification/enabled';

    /**
     * Notification sender XPATH
     */
    public const XPATH_NOTIFICATION_SENDER = 'bloomreach_engagement/notification/sender';

    /**
     * Notification recipients XPATH
     */
    public const XPATH_NOTIFICATION_RECIPIENTS = 'bloomreach_engagement/notification/recipients';

    /**
     * Notification allowed error percentage XPATH
     */
    public const XPATH_NOTIFICATION_ALLOWED_ERROR_PERCENTAGE
        = 'bloomreach_engagement/notification/allowed_error_percentage';

    /**
     * Enable Http Basic Authentication
     */
    public const XPATH_ENABLE_HTTP_BASIC_AUTH = 'bloomreach_engagement/http_basic_auth/enabled';

    /**
     * Http Basic Authentication Username
     */
    public const XPATH_HTTP_BASIC_AUTH_USERNAME = 'bloomreach_engagement/http_basic_auth/username';

    /**
     * Http Basic Authentication Password
     */
    public const XPATH_HTTP_BASIC_AUTH_PASSWORD = 'bloomreach_engagement/http_basic_auth/password';

    /**
     * Use static IPs
     */
    public const XPATH_USE_STATIC_IPS = 'bloomreach_engagement/general/use_static_ips';

    /**
     * Feed enabled config type
     */
    public const FEED_ENABLED_TYPE = 'feed_enabled';

    /**
     * Import ID config type
     */
    public const IMPORT_ID_TYPE = 'import_id';

    /**
     * Catalog ID config type
     */
    public const CATALOG_ID_TYPE = 'catalog_id';

    /**
     * Real time update type
     */
    public const REALTIME_UPDATE_TYPE = 'realtime_update';

    /**
     * Searchable fields type
     */
    public const SEARCHABLE_FIELDS = 'searchable_fields';

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
     * Get Catalog Products Feed import ID system value
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getProductsFeedImportId(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(self::XPATH_PRODUCTS_IMPORT_ID, $scopeType, $scopeCode);
    }

    /**
     * Get Catalog Product Variants Feed import ID system value
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string|null
     */
    public function getVariantsFeedImportId(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(self::XPATH_VARIANTS_IMPORT_ID, $scopeType, $scopeCode);
    }

    /**
     * Get Customers Feed import ID system value
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getCustomersFeedImportId(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(self::XPATH_CUSTOMER_IMPORT_ID, $scopeType, $scopeCode);
    }

    /**
     * Get Purchase Feed import ID system value
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getPurchaseFeedImportId(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(self::XPATH_PURCHASE_IMPORT_ID, $scopeType, $scopeCode);
    }

    /**
     * Get Purchase Items Feed  import ID system value
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getPurchaseItemFeedImportId(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(
            self::XPATH_PURCHASE_ITEMS_IMPORT_ID,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get Catalog Product Catalog ID
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getCatalogProductsId(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(self::XPATH_CATALOG_PRODUCTS_ID, $scopeType, $scopeCode);
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
     * Checks whether cart update tracking is enabled
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return bool
     */
    public function isCartUpdateTrackingEnabled(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::XPATH_ENABLE_CART_UPDATE_TRACKING, $scopeType, $scopeCode);
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
     * Checks whether is enabled DataLayer system value
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return bool
     */
    public function isDataLayerEnabled(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::XPATH_ENABLE_DATALAYER, $scopeType, $scopeCode);
    }

    /**
     * Get Catalog Product Variants Catalog ID
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
        return (string) $this->scopeConfig->getValue(self::XPATH_CATALOG_VARIANTS_ID, $scopeType, $scopeCode);
    }

    /**
     * Get is enable Debug Mode
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return bool
     */
    public function isDebugModeEnabled(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::XPATH_ENABLE_DEBUG_MODE, $scopeType, $scopeCode);
    }

    /**
     * Get Request Timeout
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return int
     */
    public function getRequestTimeout(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): int {
        return (int) $this->scopeConfig->getValue(self::XPATH_REQUEST_TIMEOUT, $scopeType, $scopeCode);
    }

    /**
     * Get Base Wait Time
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return int
     */
    public function getRetryBaseWaitTime(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): int {
        return (int) $this->scopeConfig->getValue(self::XPATH_RETRY_BASE_WAIT_TIME, $scopeType, $scopeCode);
    }

    /**
     * Get Max Wait Time
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return int
     */
    public function getRetryMaxWaitTime(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): int {
        return (int) $this->scopeConfig->getValue(self::XPATH_RETRY_MAX_WAIT_TIME, $scopeType, $scopeCode);
    }

    /**
     * Get Catalog Products Feed Enabled
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return bool
     */
    public function isProductsFeedEnabled(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::XPATH_PRODUCTS_FEED_ENABLED, $scopeType, $scopeCode);
    }

    /**
     * Get Catalog Product Variants Feed Enabled
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return bool
     */
    public function isVariantsFeedEnabled(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::XPATH_VARIANTS_FEED_ENABLED, $scopeType, $scopeCode);
    }

    /**
     * Get Customers Feed Enabled
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return bool
     */
    public function isCustomersFeedEnabled(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::XPATH_CUSTOMERS_FEED_ENABLED, $scopeType, $scopeCode);
    }

    /**
     * Get Purchase Feed Enabled
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return bool
     */
    public function isPurchaseFeedEnabled(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::XPATH_PURCHASE_FEED_ENABLED, $scopeType, $scopeCode);
    }

    /**
     * Get Purchase Items Feed Enabled
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return bool
     */
    public function isPurchaseItemsFeedEnabled(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::XPATH_PURCHASE_ITEMS_FEED_ENABLED, $scopeType, $scopeCode);
    }

    /**
     * Get Customer ID mapping
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getCustomerIdFieldMapping(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(
            self::XPATH_REGISTERED_MAPPING_CUSTOMER_ID_FIELD,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get Email mapping
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string|array
     */
    public function getEmailFieldMapping(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(
            self::XPATH_REGISTERED_MAPPING_EMAIL_FIELD,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Is notification enabled
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return bool
     */
    public function isNotificationEnabled(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::XPATH_NOTIFICATION_ENABLED, $scopeType, $scopeCode);
    }

    /**
     * Get notification sender
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getNotificationSender(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return (string)$this->scopeConfig->getValue(self::XPATH_NOTIFICATION_SENDER, $scopeType, $scopeCode);
    }

    /**
     * Get notification recipients
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getNotificationRecipients(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return (string)$this->scopeConfig->getValue(self::XPATH_NOTIFICATION_RECIPIENTS, $scopeType, $scopeCode);
    }

    /**
     * Get allowed error percentage for notification
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return int
     */
    public function getNotificationAllowedErrorPercentage(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): int {
        return (int)$this->scopeConfig->getValue(
            self::XPATH_NOTIFICATION_ALLOWED_ERROR_PERCENTAGE,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get Enable Http Basic Auth config
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return bool
     */
    public function isHttpBasicAuthEnabled(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::XPATH_ENABLE_HTTP_BASIC_AUTH, $scopeType, $scopeCode);
    }

    /**
     * Get Http Basic Auth Username
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getHttpBasicAuthUsername(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(self::XPATH_HTTP_BASIC_AUTH_USERNAME, $scopeType, $scopeCode);
    }

    /**
     * Get Http Basic Auth Password
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return string
     */
    public function getHttpBasicAuthPassword(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(self::XPATH_HTTP_BASIC_AUTH_PASSWORD, $scopeType, $scopeCode);
    }

    /**
     * Get Use Static Ips config
     *
     * @param string $scopeType
     * @param int|string $scopeCode
     *
     * @return bool
     */
    public function isUseStaticIps(
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::XPATH_USE_STATIC_IPS, $scopeType, $scopeCode);
    }
}
