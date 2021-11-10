<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Integration;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;

/**
 * The class responsible to preparing URL endpoint
 */
class GetEndpointService
{
    /**
     * Endpoint pattern '/data/v2/projects/{projectToken}/imports/{import_id}/start'
     */
    public const URL_ENDPOINT_PATTERN = '%s/data/v2/projects/%s/imports/%s/start';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @param ConfigProvider $configProvider
     */
    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * Preparing URL endpoint
     *
     * @param string $importId
     *
     * @return string
     */
    public function execute($importId): string
    {
        $apiBaseUrl = $this->configProvider->getApiTarget();
        $projectToken = $this->configProvider->getProjectTokenId();

        return sprintf(self::URL_ENDPOINT_PATTERN, $apiBaseUrl, $projectToken, $importId);
    }
}
