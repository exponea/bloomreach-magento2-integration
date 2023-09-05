<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Validator;

use Bloomreach\EngagementConnector\Service\Integration\Validator\CredentialValidator;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\Validation\ValidationException;

/**
 * The class is responsible for validating the basic settings
 */
class BaseSettings
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CredentialValidator
     */
    private $credentialValidator;

    /**
     * @param ConfigProvider $configProvider
     * @param CredentialValidator $credentialValidator
     */
    public function __construct(
        ConfigProvider $configProvider,
        CredentialValidator $credentialValidator
    ) {
        $this->configProvider = $configProvider;
        $this->credentialValidator = $credentialValidator;
    }

    /**
     * Validate basic settings
     *
     * @throws ValidationException
     */
    public function validate(): void
    {
        if (!$this->configProvider->isEnabled()) {
            throw new ValidationException(
                __('Bloomreach EngagementConnector is disabled. Enable it in the configuration and try again.')
            );
        }

        $validationResult = $this->credentialValidator->execute();

        if (!$validationResult->isValid()) {
            throw new ValidationException(
                __('Invalid Bloomreach EngagementConnector settings.'),
                null,
                0,
                $validationResult
            );
        }
    }
}
