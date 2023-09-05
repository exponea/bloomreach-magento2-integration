<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Request;

use InvalidArgumentException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;

/**
 * The class is responsible for building request body for import creation
 */
class BuilderComposite implements BuilderInterface
{
    private const DEFAULT_BUILDER = 'default';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $builderPool;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $builderPool
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $builderPool = []
    ) {
        $this->objectManager = $objectManager;
        $this->builderPool = $builderPool;
    }

    /**
     * Builds request body for import creation
     *
     * @param string $entityType
     * @param array $body
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function build(string $entityType, array $body = []): array
    {
        $defaultBody = $this->getBuilder(self::DEFAULT_BUILDER)->build($entityType, $body);

        return $this->getBuilder($entityType)->build($entityType, $defaultBody);
    }

    /**
     * Returns request builder
     *
     * @param string $entityType
     *
     * @return BuilderInterface
     * @throws NoSuchEntityException
     */
    private function getBuilder(string $entityType): BuilderInterface
    {
        $builder = $this->builderPool[$entityType] ?? null;

        if ($builder === null) {
            throw new NoSuchEntityException(
                __(
                    'There is no such request builder for %entity_type entity for interface %interface',
                    [
                        'entity_type' => $entityType,
                        'interface' => BuilderInterface::class
                    ]
                )
            );
        }

        $builder = $this->objectManager->create($builder);

        if (!($builder instanceof BuilderInterface)) {
            throw new InvalidArgumentException(
                __(
                    'Request builder "%request_builder" must implement interface %interface',
                    [
                        'request_builder' => get_class($builder),
                        'interface' => BuilderInterface::class
                    ]
                )->render()
            );
        }

        return $builder;
    }
}
