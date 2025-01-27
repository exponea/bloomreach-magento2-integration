<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping;

use Bloomreach\EngagementConnector\Service\ValueTypeGetter;
use Magento\Framework\Exception\NotFoundException;

/**
 * This class is responsible for getting field type
 */
class FieldTypeResolver
{
    /**
     * @var ConfigResolver
     */
    private $configResolver;

    /**
     * @var ValueTypeGetter
     */
    private $valueTypeGetter;

    /**
     * @param ConfigResolver $configResolver
     * @param ValueTypeGetter $valueTypeGetter
     */
    public function __construct(
        ConfigResolver $configResolver,
        ValueTypeGetter $valueTypeGetter
    ) {
        $this->configResolver = $configResolver;
        $this->valueTypeGetter = $valueTypeGetter;
    }

    /**
     * Get field type
     *
     * @param string $entityType
     * @param string $fieldCode
     * @param mixed $value
     *
     * @return string
     * @throws NotFoundException
     */
    public function get(string $entityType, string $fieldCode, $value): string
    {
        $fieldConfig = $this->configResolver->getByEntityType($entityType)[$fieldCode] ?? null;

        return $fieldConfig && $fieldConfig->getType()
            ? $fieldConfig->getType()
            : $this->valueTypeGetter->execute($value);
    }
}
