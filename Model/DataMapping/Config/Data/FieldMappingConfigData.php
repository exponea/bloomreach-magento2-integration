<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\Config\Data;

use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Contains data for field mapping configuration
 */
class FieldMappingConfigData extends AbstractSimpleObject implements FieldMappingConfigDataInterface
{
    public const BLOOMREACH_CODE = 'bloomreach_code';

    public const FIELD = 'field';

    /**
     * Return Bloomreach code
     *
     * @return string
     */
    public function getBloomreachCode(): string
    {
        return (string) $this->_get(self::BLOOMREACH_CODE);
    }

    /**
     * Returns field to map
     *
     * @return string
     */
    public function getField(): string
    {
        return $this->_get(self::FIELD);
    }
}
