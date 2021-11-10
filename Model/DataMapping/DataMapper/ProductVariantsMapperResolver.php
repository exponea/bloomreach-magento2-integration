<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\DataMapper;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\Data\FieldMappingConfigDataInterface;
use Bloomreach\EngagementConnector\Model\DataMapping\MappingProcessor;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;

/**
 * Maps Product variants data to Bloomreach data
 */
class ProductVariantsMapperResolver implements DataMapperInterface
{
    private const ENTITY_TYPE = 'catalog_product_variants';

    /**
     * @var MappingProcessor
     */
    private $mappingProcessor;

    /**
     * @param MappingProcessor $mappingProcessor
     */
    public function __construct(MappingProcessor $mappingProcessor)
    {
        $this->mappingProcessor = $mappingProcessor;
    }

    /**
     * Maps product variants data to Bloomreach data
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param FieldMappingConfigDataInterface[] $mapConfig
     *
     * @return DataObject
     */
    public function map($entity, array $mapConfig): DataObject
    {
        return $this->mappingProcessor->map($entity, self::ENTITY_TYPE, $mapConfig);
    }
}
