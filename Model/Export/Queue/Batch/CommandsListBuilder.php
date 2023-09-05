<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Queue\Batch;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Model\Export\Queue\Batch\Command\Data\Builder\BuilderInterface as DataBuilder;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * The class is responsible for building list of commands
 */
class CommandsListBuilder
{
    /**
     * @var CommandNameGetter
     */
    private $commandNameGetter;

    /**
     * @var DataBuilder
     */
    private $dataBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CommandNameGetter $commandNameGetter
     * @param DataBuilder $dataBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        CommandNameGetter $commandNameGetter,
        DataBuilder $dataBuilder,
        LoggerInterface $logger
    ) {
        $this->dataBuilder = $dataBuilder;
        $this->commandNameGetter = $commandNameGetter;
        $this->logger = $logger;
    }

    /**
     * Builds list of commands
     *
     * @param ExportQueueInterface[] $exportQueueList
     *
     * @return array
     */
    public function build(array $exportQueueList): array
    {
        $commands = [];

        foreach ($exportQueueList as $exportQueueItem) {
            try {
                $command = [
                    'name' => $this->commandNameGetter->get($exportQueueItem),
                    'command_id' => (string) $exportQueueItem->getEntityId(),
                    'data' => $this->dataBuilder->build($exportQueueItem)
                ];

                $commands[] = $command;
            } catch (LocalizedException $e) {
                $exportQueueItem->setErrorMessage($e->getMessage());
                $this->logger->error(
                    __(
                        'An error occurred while building event command for export queue item with ID: %entity_id. '
                        . 'Original error message: %error_message',
                        [
                            'entity_id' => $exportQueueItem->getEntityId(),
                            'error_message' => $e->getMessage()
                        ]
                    )
                );
            }

        }

        return $commands;
    }
}
