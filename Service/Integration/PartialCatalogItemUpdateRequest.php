<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Integration;

use Magento\Framework\Webapi\Rest\Request;

/**
 * Api request for partial update catalog item
 */
class PartialCatalogItemUpdateRequest extends UpdateCatalogItemRequest
{
    /**
     * Endpoint pattern '/data/v2/projects/{projectToken}/catalogs/{catalogId}/items/{itemId}/partial-update'
     */
    public const URL_ENDPOINT_PATTERN = '%s/data/v2/projects/%s/catalogs/%s/items/%s/partial-update';

    public const REQUEST_TYPE = Request::HTTP_METHOD_POST;
}
