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
 * Maps Customer data to Bloomreach data
 */
class Customer implements DataMapperInterface
{
    public const ENTITY_TYPE = 'customer';

    /**
     * @var MappingProcessor
     */
    private $mappingProcessor;

    /**
     * @var RegisteredMapper
     */
    private $registeredMapper;

    /**
     * @param MappingProcessor $mappingProcessor
     * @param RegisteredMapper $registeredMapper
     */
    public function __construct(
        MappingProcessor $mappingProcessor,
        RegisteredMapper $registeredMapper
    ) {
        $this->mappingProcessor = $mappingProcessor;
        $this->registeredMapper = $registeredMapper;
    }

    /**
     * Maps Order data to Bloomreach data
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param FieldMappingConfigDataInterface[] $mapConfig
     *
     * @return DataObject
     */
    public function map($entity, array $mapConfig): DataObject
    {
        return $this->registeredMapper->map(
            $this->mappingProcessor->map($entity, self::ENTITY_TYPE, $mapConfig)
        );
    }
}
