<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Api;

use Bloomreach\EngagementConnector\Api\Data\InitialExportStatusInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Gets Initial Export Status
 */
interface GetInitialExportStatusInterface
{
    /**
     * Get Initial Import Status
     *
     * @param string $entityType
     *
     * @return InitialExportStatusInterface
     * @throws NoSuchEntityException
     */
    public function execute(string $entityType): InitialExportStatusInterface;
}
