<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Integration\Validator;

use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;

/**
 * The class is responsible for validating the required credentials
 */
class CredentialValidator
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param ConfigProvider $configProvider
     * @param ValidationResultFactory $validationResultFactory
     */
    public function __construct(
        ConfigProvider $configProvider,
        ValidationResultFactory $validationResultFactory
    ) {
        $this->configProvider = $configProvider;
        $this->validationResultFactory = $validationResultFactory;
    }

    /**
     * Validates required credentials
     *
     * @return void
     */
    public function execute(): ValidationResult
    {
        $errors = [];

        if (!filter_var($this->configProvider->getApiTarget(), FILTER_VALIDATE_URL)) {
            $errors[] = __('API Target Url is not valid.');
        }

        if (!$this->configProvider->getApiKeyId()) {
            $errors[] = __('API Key ID is empty.');
        }

        if (!$this->configProvider->getApiSecret()) {
            $errors[] = __('API Secret is empty.');
        }

        if (!$this->configProvider->getProjectTokenId()) {
            $errors[] = __('Project Token is empty.');
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
