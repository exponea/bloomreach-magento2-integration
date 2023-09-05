<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Request\Entity;

use Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Catalog\CatalogNameGetter;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Request\BuilderInterface;

/**
 * The class is responsible for building request body for Catalog entity type
 */
class Catalog implements BuilderInterface
{
    /**
     * @var CatalogNameGetter
     */
    private $catalogNameGetter;

    /**
     * @param CatalogNameGetter $catalogNameGetter
     */
    public function __construct(CatalogNameGetter $catalogNameGetter)
    {
        $this->catalogNameGetter = $catalogNameGetter;
    }

    /**
     * Builds request body for Catalog entity type
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
            'catalog_destination' => [
                'catalog_name' => $this->catalogNameGetter->execute($entityType),
                'catalog_attributes' => [
                    'catalog_type' => 'product'
                ]
            ]
        ];

        return $body;
    }
}
