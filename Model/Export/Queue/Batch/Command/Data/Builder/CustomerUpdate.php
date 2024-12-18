<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Queue\Batch\Command\Data\Builder;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * The class is responsible for building the customer update command data
 */
class CustomerUpdate implements BuilderInterface
{
    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @param SerializerInterface $jsonSerializer
     */
    public function __construct(SerializerInterface $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Builds command data
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return array
     * @throws LocalizedException
     */
    public function build(ExportQueueInterface $exportQueue): array
    {
        $properties = $this->unserializeData($exportQueue->getBody());

        return [
            'customer_ids' => $this->jsonSerializer->unserialize($exportQueue->getRegistered()),
            'properties' => $properties,
            'update_timestamp' => strtotime($exportQueue->getCreatedAt())
        ];
    }

    /**
     * Unserialize data
     *
     * @param string|array $data
     *
     * @return array|string
     * @throws LocalizedException
     */
    private function unserializeData($data)
    {
        try {
            return $this->jsonSerializer->unserialize($data);
        } catch (InvalidArgumentException $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}
