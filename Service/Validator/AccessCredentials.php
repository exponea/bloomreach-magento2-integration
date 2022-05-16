<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Validator;

use Bloomreach\EngagementConnector\Logger\Debugger;
use Bloomreach\EngagementConnector\Service\Integration\GetSystemTimeOfPlatform;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;

/**
 * The class is responsible for validating access credentials
 */
class AccessCredentials
{
    private const FAILED_AUTH_ERROR_CODES = [400, 401, 502];

    /**
     * @var GetSystemTimeOfPlatform
     */
    private $getSystemTimeOfPlatform;

    /**
     * @var Debugger
     */
    private $debugger;

    /**
     * @param GetSystemTimeOfPlatform $getSystemTimeOfPlatform
     * @param Debugger $debugger
     */
    public function __construct(
        GetSystemTimeOfPlatform $getSystemTimeOfPlatform,
        Debugger $debugger
    ) {
        $this->getSystemTimeOfPlatform = $getSystemTimeOfPlatform;
        $this->debugger = $debugger;
    }

    /**
     * Validate access credentials
     *
     * @return void
     * @throws ValidatorException
     * @throws LocalizedException
     */
    public function execute(): void
    {
        $this->debugger->log(__('Credential validation started.'));
        $response = $this->getSystemTimeOfPlatform->execute();
        $this->debugger->log(__('Credentials validation complete.'));

        if (in_array($response->getStatusCode(), self::FAILED_AUTH_ERROR_CODES)) {
            throw new ValidatorException(__($response->getReasonPhrase()));
        } elseif ($response->getStatusCode() !== 200) {
            throw new LocalizedException(__($response->getReasonPhrase()));
        }
    }
}
