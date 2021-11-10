<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Integration;

/**
 * Api request for update customer
 */
class UpdateCustomerRequest extends SendEventRequest
{
    /**
     * Endpoint pattern '/track/v2/projects/{projectToken}/customers'
     */
    public const URL_ENDPOINT_PATTERN = '%s/track/v2/projects/%s/customers';
}
