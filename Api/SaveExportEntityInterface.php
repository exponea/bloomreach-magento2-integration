<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Api;

use Bloomreach\EngagementConnector\Api\Data\ExportEntityInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Saves export entity
 */
interface SaveExportEntityInterface
{
    /**
     * Saves export entity
     *
     * @param ExportEntityInterface $exportEntity
     *
     * @return ExportEntityInterface
     * @throws CouldNotSaveException
     */
    public function execute(ExportEntityInterface $exportEntity): ExportEntityInterface;
}
