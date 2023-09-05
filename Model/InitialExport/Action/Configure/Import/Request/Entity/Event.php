<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Request\Entity;

use Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Request\BuilderInterface;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Request\IdMappingGetter;

/**
 * The class is responsible for building request body for Event entity type
 */
class Event implements BuilderInterface
{
    /**
     * @var IdMappingGetter
     */
    private $idMappingGetter;

    /**
     * @param IdMappingGetter $idMappingGetter
     */
    public function __construct(IdMappingGetter $idMappingGetter)
    {
        $this->idMappingGetter = $idMappingGetter;
    }

    /**
     * Builds request body for Event entity type
     *
     * @param string $entityType
     * @param array $body
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build(string $entityType, array $body = []): array
    {
        $body['destination'] = [
            'event_destination' => [
                'event_type' => $entityType,
                'event_triggers' => false
            ]
        ];
        $body['mapping']['column_mapping']['id_mappings'] = $this->idMappingGetter->execute();

        return $body;
    }
}
