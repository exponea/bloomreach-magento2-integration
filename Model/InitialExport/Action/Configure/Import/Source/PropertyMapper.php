<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Source;

use Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Catalog\FieldsMapper;
use Bloomreach\EngagementConnector\Service\ValueTypeGetter;

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
     * @param ValueTypeGetter $valueTypeGetter
     */
    public function __construct(ValueTypeGetter $valueTypeGetter)
    {
        $this->valueTypeGetter = $valueTypeGetter;
    }

    /**
     * Maps data
     *
     * @param array $data
     *
     * @return array
     */
    public function map(array $data): array
    {
        $result = [];
        $iterator = 1;

        foreach ($data as $column => $value) {
            $result[] = [
                'from_column' => $column,
                'to_column' => $column,
                'target_type' => $this->valueTypeGetter->execute($value),
                'searchable' => $column !== self::PRIMARY_ID && FieldsMapper::MAX_SEARCHABLE >= $iterator,
                'indexed' => null
            ];

            if ($column !== self::PRIMARY_ID) {
                $iterator++;
            }
        }

        return $result;
    }
}
