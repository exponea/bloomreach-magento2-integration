<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Model\DataMapping\DataMapper;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\Data\FieldMappingConfigDataInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;

/**
 * Map Magento Entity data to Bloomreach data
 */
interface DataMapperInterface
{
    /**
     * Map Magento Entity data to Bloomreach data
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param FieldMappingConfigDataInterface[] $mapConfig
     *
     * @return DataObject
     */
    public function map($entity, array $mapConfig): DataObject;
}
