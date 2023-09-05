<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Api;

use Magento\Framework\Exception\CouldNotDeleteException;

/**
 * Deletes Initial Export Status
 */
interface DeleteInitialExportStatusInterface
{
    /**
     * Deletes Initial Export Status
     *
     * @param string $entityType
     *
     * @return void
     * @throws CouldNotDeleteException
     */
    public function execute(string $entityType): void;
}
