<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Integration;

/**
 * The class is responsible for sending batch of commands to the Bloomreach
 */
class BatchCommandsRequest extends SendEventRequest
{
    /**
     * Endpoint pattern '/track/v2/projects/{projectToken}/batch'
     */
    public const URL_ENDPOINT_PATTERN = '%s/track/v2/projects/%s/batch';
}
