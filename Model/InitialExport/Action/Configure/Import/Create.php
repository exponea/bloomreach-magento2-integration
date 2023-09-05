<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import;

use Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Request\BuilderInterface;
use Bloomreach\EngagementConnector\Service\Integration\CreateImport;
use Magento\Framework\Exception\LocalizedException;

/**
 * The class is responsible for creating an import
 */
class Create
{
    /**
     * @var CreateImport
     */
    private $createImport;
    /**
     * @var BuilderInterface
     */
    private $requestBuilder;

    /**
     * @param CreateImport $createImport
     * @param BuilderInterface $requestBuilder
     */
    public function __construct(
        CreateImport $createImport,
        BuilderInterface $requestBuilder
    ) {
        $this->createImport = $createImport;
        $this->requestBuilder = $requestBuilder;
    }

    /**
     * Creates an import
     *
     * @param string $entityType
     *
     * @return string
     * @throws LocalizedException
     */
    public function execute(string $entityType): string
    {
        return $this->createImport->execute($this->requestBuilder->build($entityType));
    }
}
