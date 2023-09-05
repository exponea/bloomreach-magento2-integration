<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Queue\Batch\Command\Data\Builder;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use InvalidArgumentException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;

/**
 * The class is responsible for building command data depending on the API type and entity type
 */
class BuilderResolver implements BuilderInterface
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
        $this->builderPool = $builderPool;
        $this->objectManager = $objectManager;
    }

    /**
     * Builds command data
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function build(ExportQueueInterface $exportQueue): array
    {
        return $this->getBuilder($exportQueue)->build($exportQueue);
    }

    /**
     * Get command data builder
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return BuilderInterface
     * @throws NoSuchEntityException
     */
    private function getBuilder(ExportQueueInterface $exportQueue): BuilderInterface
    {
        $builderName = isset($this->builderPool[$exportQueue->getApiType()][$exportQueue->getEntityType()])
            ? $exportQueue->getEntityType() : self::DEFAULT_BUILDER;

        $builderClass = $this->builderPool[$exportQueue->getApiType()][$builderName] ?? null;

        if ($builderClass === null) {
            throw new NoSuchEntityException(
                __(
                    'There is no such command data builder for such API type %api_type, %entity_type entity type',
                    [
                        'api_type' => $exportQueue->getApiType(),
                        'entity_type' => $exportQueue->getEntityType()
                    ]
                )
            );
        }

        $builder = $this->objectManager->get($builderClass);

        if (!($builder instanceof BuilderInterface)) {
            throw new InvalidArgumentException(
                __(
                    'Command data builder "%request_builder" must implement interface %interface',
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
