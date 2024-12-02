<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Config\Source;

use Bloomreach\EngagementConnector\Model\DataMapping\ConfigResolver;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\DefaultType;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Catalog\FieldsMapper;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * 'catalog_product' fields source
 */
class CatalogProductFields implements OptionSourceInterface
{
    /**
     * @var ConfigResolver
     */
    private $configResolver;

    /**
     * @param ConfigResolver $configResolver
     */
    public function __construct(ConfigResolver $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * Get options
     *
     * @return array
     * @throws NotFoundException
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->configResolver->getByEntityType($this->getEntityType()) as $item) {
            if ($item->getBloomreachCode() === FieldsMapper::PRIMARY_ID) {
                continue;
            }

            $options[] = [
                'label' => $item->getBloomreachCode(),
                'value' => $item->getBloomreachCode()
            ];
        }

        return $options;
    }

    /**
     * Get entity type
     *
     * @return string
     */
    protected function getEntityType(): string
    {
        return DefaultType::ENTITY_TYPE;
    }
}
