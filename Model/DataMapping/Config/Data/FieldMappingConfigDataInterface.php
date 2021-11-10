<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Model\DataMapping\Config\Data;

/**
 * Contains data for field mapping configuration
 */
interface FieldMappingConfigDataInterface
{
    /**
     * Return Bloomreach code
     *
     * @return string
     */
    public function getBloomreachCode(): string;

    /**
     * Returns field to map
     *
     * @return string
     */
    public function getField(): string;
}
