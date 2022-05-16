<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Block\Adminhtml\System\Config;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;

/**
 * The class is responsible for rendering Validate Credentials Button
 */
class CredentialValidatorButton extends Button
{
    /**
     * Configurations required to enable the button
     */
    public const REQUIRED_FIELDS = [
        ConfigProvider::XPATH_API_KEY_ID,
        ConfigProvider::XPATH_API_SECRET,
        ConfigProvider::XPATH_API_TARGET,
        ConfigProvider::XPATH_PROJECT_TOKEN_ID
    ];
}
