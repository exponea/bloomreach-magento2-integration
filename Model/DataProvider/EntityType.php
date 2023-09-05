<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataProvider;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Contains all entity types
 */
class EntityType implements OptionSourceInterface
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
        return array_keys($this->entityTypes);
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        foreach ($this->entityTypes as $entityType => $label) {
            $result[] = [
                'label' => __($label),
                'value' => $entityType
            ];
        }

        return $result;
    }

    /**
     * Get entity name
     *
     * @param string $entityType
     *
     * @return string
     */
    public function getEntityName(string $entityType): string
    {
        return $this->entityTypes[$entityType] ?? '';
    }
}
