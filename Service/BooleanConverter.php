<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service;

/**
 * Provides functionality to convert values to boolean types.
 */
class BooleanConverter
{
    /**
     * Converts value to boolean type
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return $this->convertStringToBool($value);
        }

        return !!$value;
    }

    /**
     * Convert boolean value to string
     *
     * @param bool $value
     *
     * @return string
     */
    public function toString(bool $value): string
    {
        return $value ? ValueTypeGetter::TRUE : ValueTypeGetter::FALSE;
    }

    /**
     * Converts the given string to uppercase and checks if it matches a predefined true value.
     *
     * @param string $value
     *
     * @return bool
     */
    private function convertStringToBool(string $value): bool
    {
        $uppercaseValue = strtoupper($value);

        if ($uppercaseValue === ValueTypeGetter::TRUE) {
            return true;
        }

        if ($uppercaseValue === ValueTypeGetter::FALSE) {
            return false;
        }

        return (bool) $value;
    }
}
