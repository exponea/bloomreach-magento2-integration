<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\EavAttribute;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Returns options for attribute
 */
class GetOptions
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var array
     */
    private $attributeOptionCache = [];

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Returns array with attribute options ['option_value' => 'option_label']
     *
     * @param string $attributeCode
     * @param string $entityType
     *
     * @return array
     */
    public function execute(string $attributeCode, string $entityType): array
    {
        if (isset($this->attributeOptionCache[$entityType][$attributeCode])) {
            return $this->attributeOptionCache[$entityType][$attributeCode];
        }

        $attribute = $this->getAttribute($attributeCode, $entityType);

        $attributeOptions = [];

        if ($attribute &&
            ($attribute->getFrontendInput() === 'multiselect' || $attribute->getFrontendInput() === 'select')
        ) {
            $attributeOptions = $attribute->getSource()->getAllOptions() ?: [];
        }

        $this->attributeOptionCache[$entityType][$attributeCode] = $this->mapOptionsValueWithLabel($attributeOptions);

        return $this->attributeOptionCache[$entityType][$attributeCode];
    }

    /**
     * Get attribute
     *
     * @param string $attributeCode
     * @param string $entityType
     *
     * @return AttributeInterface|null
     */
    private function getAttribute(string $attributeCode, string $entityType): ?AttributeInterface
    {
        try {
            return $this->attributeRepository->get($entityType, $attributeCode);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Maps option values with label
     *
     * @param array $options
     *
     * @return array
     */
    private function mapOptionsValueWithLabel(array $options): array
    {
        $result = [];
        foreach ($options as $option) {
            if (isset($option['value']) && $option['value']) {
                $result[$option['value']] = $option['label'];
            }
        }

        return $result;
    }
}
