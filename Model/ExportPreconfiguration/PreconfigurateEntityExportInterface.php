<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Model\ExportPreconfiguration;

use Magento\Framework\DataObject;

/**
 * Preconfigurate export entity data
 */
interface PreconfigurateEntityExportInterface
{
    /**
     * Returns export entity preconfigured data
     *
     * @return PreconfigurationResultInterface
     */
    public function execute(): PreconfigurationResultInterface;
}
