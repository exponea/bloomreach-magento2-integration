<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Source;

use Magento\Framework\DataObject;

/**
 * Import Data Source
 */
class SourceData extends DataObject implements SourceInterface
{
    public const FILE_URL = 'file_url';

    public const PROPERTY_MAPPINGS = 'property_mappings';

    /**
     * Get File Url
     *
     * @return string
     */
    public function getFileUrl(): string
    {
        return (string) $this->getData(self::FILE_URL);
    }

    /**
     * Set File Url
     *
     * @param string $fileUrl
     *
     * @return void
     */
    public function setFileUrl(string $fileUrl): void
    {
        $this->setData(self::FILE_URL, $fileUrl);
    }

    /**
     * Get Property Mappings
     *
     * @return array
     */
    public function getPropertyMappings(): array
    {
        $mapping = $this->getData(self::PROPERTY_MAPPINGS);

        return is_array($mapping) ? $mapping : [];
    }

    /**
     * Set Property Mappings
     *
     * @param array $propertyMappings
     *
     * @return void
     */
    public function setPropertyMappings(array $propertyMappings): void
    {
        $this->setData(self::PROPERTY_MAPPINGS, $propertyMappings);
    }
}
