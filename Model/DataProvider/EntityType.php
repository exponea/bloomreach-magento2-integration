<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataProvider;

/**
 * Contains all entity types
 */
class EntityType
{
    /**
     * @var array
     */
    private $entityTypes;

    /**
     * @param array $entityTypes
     */
    public function __construct(array $entityTypes)
    {
        $this->entityTypes = $entityTypes;
    }

    /**
     * Returns all entity types
     *
     * @return array
     */
    public function getAllTypes(): array
    {
        return $this->entityTypes;
    }
}
