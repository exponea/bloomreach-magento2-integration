<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Source;

/**
 * Import source data
 */
interface SourceInterface
{
    /**
     * Get File Url
     *
     * @return string
     */
    public function getFileUrl(): string;

    /**
     * Set File Url
     *
     * @param string $fileUrl
     *
     * @return void
     */
    public function setFileUrl(string $fileUrl): void;

    /**
     * Get Property Mappings
     *
     * @return array
     */
    public function getPropertyMappings(): array;

    /**
     * Set Property Mappings
     *
     * @param array $propertyMappings
     *
     * @return void
     */
    public function setPropertyMappings(array $propertyMappings): void;
}
