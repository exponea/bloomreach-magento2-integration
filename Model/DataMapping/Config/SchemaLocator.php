<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\Config;

use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Module\Dir;

/**
 * Config schema locator
 */
class SchemaLocator implements SchemaLocatorInterface
{
    /**
     * XML schema for config file.
     */
    private const CONFIG_FILE_SCHEMA = 'bloomreach_entity_mapping.xsd';

    /**
     * @var string
     */
    private $schema;

    /**
     * @var string
     */
    private $perFileSchema;

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @param Reader $moduleReader
     */
    public function __construct(Reader $moduleReader)
    {
        $this->moduleReader = $moduleReader;
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function getSchema(): ?string
    {
        if ($this->schema === null) {
            $this->schema = $this->getEtcDir() . DIRECTORY_SEPARATOR . self::CONFIG_FILE_SCHEMA;
        }

        return $this->schema;
    }

    /**
     * Get path to per file validation schema
     *
     * @return string|null
     */
    public function getPerFileSchema(): ?string
    {
        if (null === $this->perFileSchema) {
            $this->perFileSchema = $this->getSchema();
        }

        return $this->perFileSchema;
    }

    /**
     * Return path to etc dir
     *
     * @return string
     */
    private function getEtcDir(): string
    {
        return $this->moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Bloomreach_EngagementConnector');
    }
}
