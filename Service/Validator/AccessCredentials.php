<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Validator;

use Bloomreach\EngagementConnector\Exception\AuthenticationException;
use Bloomreach\EngagementConnector\Exception\AuthorizationException;
use Bloomreach\EngagementConnector\Exception\BadRequestException;
use Bloomreach\EngagementConnector\Exception\NotFoundException;
use Bloomreach\EngagementConnector\Logger\Debugger;
use Bloomreach\EngagementConnector\Service\Integration\GetSystemTimeOfPlatform;
use Magento\Framework\Validation\ValidationException;

/**
 * The class is responsible for validating access credentials
 */
class AccessCredentials
{
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
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws BadRequestException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function validate(): void
    {
        $this->debugger->log(__('Credential validation started.'));
        $this->getSystemTimeOfPlatform->execute();
        $this->debugger->log(__('Credentials validation complete.'));
    }
}
