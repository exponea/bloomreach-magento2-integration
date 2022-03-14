<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Integration;

/**
 * Api request for anonymize customer
 */
class AnonymizeCustomerRequest extends SendEventRequest
{
    /**
     * Endpoint pattern '/data/v2/projects/{projectToken}/customers/anonymize_bulk'
     */
    public const URL_ENDPOINT_PATTERN = '%s/data/v2/projects/%s/customers/anonymize_bulk';
}
