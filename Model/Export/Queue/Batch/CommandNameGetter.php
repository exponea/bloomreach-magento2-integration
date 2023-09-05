<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Queue\Batch;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * The class is responsible for returning command name from pool
 */
class CommandNameGetter
{
    /**
     * @var array
     */
    private $commandNamePool;

    /**
     * @param array $commandNamePool
     */
    public function __construct(array $commandNamePool = [])
    {
        $this->commandNamePool = $commandNamePool;
    }

    /**
     * Get command name
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return string
     * @throws LocalizedException
     */
    public function get(ExportQueueInterface $exportQueue): string
    {
        $commandName = $this->commandNamePool[$exportQueue->getApiType()][$exportQueue->getEntityType()] ?? null;

        if ($commandName === null) {
            throw new LocalizedException(
                __(
                    'There is no such command name for such API type %api_type, %entity_type entity type',
                    [
                        'api_type' => $exportQueue->getApiType(),
                        'entity_type' => $exportQueue->getEntityType()
                    ]
                )
            );
        }

        return $commandName;
    }
}
