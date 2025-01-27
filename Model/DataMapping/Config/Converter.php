<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\Config;

use DOMDocument;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\Config\ConverterInterface;

/**
 * Config Converter
 */
class Converter implements ConverterInterface
{
    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @param BooleanUtils $booleanUtils
     */
    public function __construct(BooleanUtils $booleanUtils)
    {
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * Convert config
     *
     * @param DOMDocument $source
     *
     * @return array
     */
    public function convert($source)
    {
        $output = [];

        if (!$source instanceof DOMDocument) {
            return $output;
        }

        $entityTypes = $source->getElementsByTagName('entity_type');
        /** @var $entityType DOMElement */
        foreach ($entityTypes as $entityType) {
            $entityTypeData = [];
            foreach ($entityType->childNodes as $bloomreachCode) {
                if ($bloomreachCode->nodeType != XML_ELEMENT_NODE || $this->isDisabled($bloomreachCode)) {
                    continue;
                }
                $fieldCode = '';
                foreach ($bloomreachCode->childNodes as $field) {
                    if ($field->nodeType != XML_ELEMENT_NODE || $this->isDisabled($field)) {
                        continue;
                    }

                    $fieldCode = $field->getAttribute('code');
                }

                $entityTypeData[] = [
                    'bloomreach_code' => $bloomreachCode->getAttribute('code'),
                    'field' => $fieldCode,
                    'type' => $bloomreachCode->getAttribute('type'),
                ];
            }
            $output[$entityType->getAttribute('entity')] = $entityTypeData;
        }

        return $output;
    }

    /**
     * Checks whether is node disabled
     *
     * @param DOMElement $node
     *
     * @return bool
     */
    private function isDisabled($node): bool
    {
        $disabled = $node->getAttribute('disabled');

        return $disabled && $this->booleanUtils->toBoolean($disabled);
    }
}
