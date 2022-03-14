<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\InitialImport;

use Bloomreach\EngagementConnector\Model\ResourceModel\ExportEntity\CollectionFactory;

/**
 * Checking is Entities for Import exists
 */
class ProcessStatus
{
    /**
     * @var CollectionFactory
     */
    private $exportEntityCollectionFactory;

    /**
     * @param CollectionFactory $exportEntityCollectionFactory
     */
    public function __construct(
        CollectionFactory $exportEntityCollectionFactory
    ) {
        $this->exportEntityCollectionFactory = $exportEntityCollectionFactory;
    }

    /**
     * Checking if exist records in Export Entity table
     *
     * @return bool
     */
    public function execute(): bool
    {
        $collection = $this->exportEntityCollectionFactory->create();
        return (bool) $collection->setPageSize(1)->count();
    }
}
