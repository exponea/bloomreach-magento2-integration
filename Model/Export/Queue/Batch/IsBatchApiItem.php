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
 * The class is responsible for checking whether the item can be sent in a batch
 */
class IsBatchApiItem
{
    /**
     * @var CommandNameGetter
     */
    private $commandNameGetter;

    /**
     * @param CommandNameGetter $commandNameGetter
     */
    public function __construct(CommandNameGetter $commandNameGetter)
    {
        $this->commandNameGetter = $commandNameGetter;
    }

    /**
     * Checks whether the item can be sent in a batch
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return bool
     */
    public function execute(ExportQueueInterface $exportQueue): bool
    {
        try {
            return !!$this->commandNameGetter->get($exportQueue);
        } catch (LocalizedException $e) {
            return false;
        }
    }
}
