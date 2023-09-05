<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Catalog;

use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;

/**
 * The class is responsible for getting a catalog name
 */
class CatalogNameGetter
{
    private const NAME_PLACEHOLDER = '%s Catalog';

    /**
     * @var EntityType
     */
    private $entityType;

    /**
     * @param EntityType $entityType
     */
    public function __construct(EntityType $entityType)
    {
        $this->entityType = $entityType;
    }

    /**
     * Returns Catalog Name
     *
     * @param string $entityType
     *
     * @return string
     */
    public function execute(string $entityType): string
    {
        return sprintf(self::NAME_PLACEHOLDER, $this->entityType->getEntityName($entityType));
    }
}
