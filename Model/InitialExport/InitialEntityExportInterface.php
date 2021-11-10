<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Model\InitialExport;

/**
 * Adds entity to the initial export
 */
interface InitialEntityExportInterface
{
    /**
     * Adds entity to the initial export
     *
     * @return void
     */
    public function execute(): void;
}
