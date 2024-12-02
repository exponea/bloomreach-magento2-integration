<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Source;

use Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Catalog\FieldsMapper;
use Bloomreach\EngagementConnector\Service\ValueTypeGetter;
use Bloomreach\EngagementConnector\System\SearchableFieldsResolver;
use Magento\Framework\Exception\LocalizedException;

/**
 * The class is responsible for mapping properties
 */
class PropertyMapper
{
    private const PRIMARY_ID = 'item_id';

    /**
     * @var ValueTypeGetter
     */
    private $valueTypeGetter;

    /**
     * @var SearchableFieldsResolver
     */
    private $searchableFieldsResolver;

    /**
     * @param ValueTypeGetter $valueTypeGetter
     * @param SearchableFieldsResolver $searchableFieldsResolver
     */
    public function __construct(
        ValueTypeGetter $valueTypeGetter,
        SearchableFieldsResolver $searchableFieldsResolver
    ) {
        $this->valueTypeGetter = $valueTypeGetter;
        $this->searchableFieldsResolver = $searchableFieldsResolver;
    }

    /**
     * Maps data
     *
     * @param array $data
     * @param string|null $entityType
     *
     * @return array
     */
    public function map(array $data, ?string $entityType = null): array
    {
        $result = [];
        $iterator = 1;
        $searchableFields = $this->getSearchableFields($entityType);
        $searchableFieldsNumber = count($searchableFields);

        foreach ($data as $column => $value) {
            $isSearchable = !($column === self::PRIMARY_ID)
                && ($searchableFieldsNumber > 0
                    ? in_array($column, $searchableFields) && FieldsMapper::MAX_SEARCHABLE >= $iterator
                    : FieldsMapper::MAX_SEARCHABLE >= $iterator
                );
            $result[] = [
                'from_column' => $column,
                'to_column' => $column,
                'target_type' => $this->valueTypeGetter->execute($value),
                'searchable' => $isSearchable,
                'indexed' => null
            ];

            if ($column !== self::PRIMARY_ID && $isSearchable) {
                $iterator++;
            }
        }

        return $result;
    }

    /**
     * Returns searchable fields
     *
     * @param string|null $entityType
     *
     * @return array
     */
    private function getSearchableFields(?string $entityType = null): array
    {
        try {
            return $entityType ? $this->searchableFieldsResolver->get($entityType) : [];
        } catch (LocalizedException $e) {
            return [];
        }
    }
}
