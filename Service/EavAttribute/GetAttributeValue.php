<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\EavAttribute;

/**
 * Returns value of attribute
 */
class GetAttributeValue
{
    /**
     * @var GetOptions
     */
    private $getOptions;

    /**
     * @param GetOptions $getOptions
     */
    public function __construct(GetOptions $getOptions)
    {
        $this->getOptions = $getOptions;
    }

    /**
     * Returns value of attribute
     *
     * @param string|array $attributeValue
     * @param string $attributeCode
     * @param string $entityType
     *
     * @return mixed|string|null
     */
    public function execute($attributeValue, string $attributeCode, string $entityType): string
    {
        if (!$attributeValue) {
            return (string) $attributeValue;
        }

        $options = $this->getOptions->execute($attributeCode, $entityType);

        if ($options) {
            $attributeValue = $this->getOptionLabelByValues($options, $attributeValue);
        }

        return is_array($attributeValue) ? implode(',', $attributeValue) : (string) $attributeValue;
    }

    /**
     * Get option label by option value
     *
     * @param array $options
     * @param string|array $attributeValue
     *
     * @return string
     */
    private function getOptionLabelByValues(array $options, $attributeValue): string
    {
        $result = [];

        if (!is_array($attributeValue)) {
            $attributeValue = explode(',', (string) $attributeValue);
        }

        foreach ($attributeValue as $value) {
            $result[] = $options[$value] ?? '';
        }

        return $result ? implode(',', $result): implode(',', $attributeValue);
    }
}
