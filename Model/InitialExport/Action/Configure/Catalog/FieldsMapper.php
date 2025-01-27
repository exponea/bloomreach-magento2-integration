<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Catalog;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldTypeResolver;
use Bloomreach\EngagementConnector\Service\ValueTypeGetter;
use Bloomreach\EngagementConnector\System\SearchableFieldsResolver;
use Magento\Framework\Exception\LocalizedException;

/**
 * The class is responsible for mapping fields
 */
class FieldsMapper
{
    public const MAX_SEARCHABLE = 20;

    public const PRIMARY_ID = 'item_id';

    /**
     * @var FieldTypeResolver
     */
    private $fieldTypeResolver;

    /**
     * @var SearchableFieldsResolver
     */
    private $searchableFieldsResolver;

    /**
     * @param FieldTypeResolver $fieldTypeResolver
     * @param SearchableFieldsResolver $searchableFieldsResolver
     */
    public function __construct(
        FieldTypeResolver $fieldTypeResolver,
        SearchableFieldsResolver $searchableFieldsResolver
    ) {
        $this->fieldTypeResolver = $fieldTypeResolver;
        $this->searchableFieldsResolver = $searchableFieldsResolver;
    }

    /**
     * Maps data
     *
     * @param array $data
     * @param string|null $entityType
     *
     * @return array
     * @throws LocalizedException
     */
    public function map(array $data, string $entityType): array
    {
        $result = [];
        $searchableFields = $entityType ? $this->searchableFieldsResolver->get($entityType) : [];
        $searchableFieldsNumber = count($searchableFields);
        $searchableNumber = 1;

        foreach ($data as $column => $value) {
            if ($column === self::PRIMARY_ID) {
                continue;
            }

            $isSearchable = $searchableFieldsNumber > 0
                ? in_array($column, $searchableFields) && self::MAX_SEARCHABLE >= $searchableNumber
                : self::MAX_SEARCHABLE >= $searchableNumber;

            $result[] = [
                'name' => $column,
                'type' => $this->fieldTypeResolver->get($entityType, $column, $value),
                'searchable' => $isSearchable
            ];

            if (!$isSearchable) {
                continue;
            }

            $searchableNumber++;
        }

        return $result;
    }
}
