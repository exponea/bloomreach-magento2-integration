<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Integration;

use Magento\Framework\Webapi\Rest\Request;

/**
 * The class is responsible for sending delete catalog item request
 */
class DeleteCatalogItemRequest extends UpdateCatalogItemRequest
{
    public const REQUEST_TYPE = Request::HTTP_METHOD_DELETE;
}
