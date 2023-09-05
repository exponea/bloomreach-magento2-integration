<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Api;

use Bloomreach\EngagementConnector\Api\Data\InitialExportStatusInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Saves Initial Export Status
 */
interface SaveInitialExportStatusInterface
{
    /**
     * Saves Initial Export Status
     *
     * @param InitialExportStatusInterface $initialExportStatus
     *
     * @return InitialExportStatusInterface
     * @throws CouldNotSaveException
     */
    public function execute(InitialExportStatusInterface $initialExportStatus): InitialExportStatusInterface;
}
