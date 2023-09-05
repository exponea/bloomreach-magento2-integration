<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Catalog;

use Bloomreach\EngagementConnector\Service\ValueTypeGetter;

/**
 * The class is responsible for mapping fields
 */
class FieldsMapper
{
    public const MAX_SEARCHABLE = 20;

    public const PRIMARY_ID = 'item_id';

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
            if ($column === self::PRIMARY_ID) {
                continue;
            }

            $result[] = [
                'name' => $column,
                'type' => $this->valueTypeGetter->execute($value),
                'searchable' => self::MAX_SEARCHABLE >= $iterator,
            ];
            $iterator++;
        }

        return $result;
    }
}
