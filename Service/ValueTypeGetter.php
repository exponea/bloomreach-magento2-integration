<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service;

/**
 * The class is responsible for getting the type of value
 */
class ValueTypeGetter
{
    private const STRING_LENGTH = 255;

    private const LIST_TYPE = 'list';

    private const BOOLEAN_TYPE = 'boolean';

    private const NUMBER_TYPE = 'number';

    private const STRING_TYPE = 'string';

    private const LONG_TEXT_TYPE = 'long text';

    private const BOOLEAN_VALUES = [
        'true',
        'false'
    ];

    /**
     * Get Value type
     *
     * @param string|int|array|bool $value
     *
     * @return string
     */
    public function execute($value): string
    {
        if (is_array($value)) {
            return self::LIST_TYPE;
        } elseif (is_numeric($value)) {
            return self::NUMBER_TYPE;
        } elseif (is_bool($value)
            || (is_string($value) && in_array(strtolower($value), self::BOOLEAN_VALUES))
        ) {
            return self::BOOLEAN_TYPE;
        }

        return is_string($value) && strlen($value) > self::STRING_LENGTH ? self::LONG_TEXT_TYPE : self::STRING_TYPE;
    }
}
