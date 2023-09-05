<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Queue\Batch\Command\Data\Builder;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Model\DataMapping\Event\RegisteredGenerator;
use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * The class is responsible for building body of default data
 */
class DefaultData implements BuilderInterface
{
    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var RegisteredGenerator
     */
    private $registeredGenerator;

    /**
     * @param SerializerInterface $jsonSerializer
     * @param RegisteredGenerator $registeredGenerator
     */
    public function __construct(
        SerializerInterface $jsonSerializer,
        RegisteredGenerator $registeredGenerator
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->registeredGenerator = $registeredGenerator;
    }

    /**
     * Build command data
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return array
     * @throws LocalizedException
     */
    public function build(ExportQueueInterface $exportQueue): array
    {
        $properties = $this->unserializeData($exportQueue->getBody());

        if (is_array($properties)) {
            $this->deleteUnusedFields($properties);
        }

        return [
            'customer_ids' => $this->unserializeData($exportQueue->getRegistered()),
            'properties' => $properties,
            'event_type' => $exportQueue->getEntityType(),
            'timestamp' => strtotime($exportQueue->getCreatedAt())
        ];
    }

    /**
     * Delete unused fields
     *
     * @param array $properties
     *
     * @return void
     */
    private function deleteUnusedFields(array &$properties): void
    {
        $this->registeredGenerator->deleteRegisteredData($properties);

        if (isset($properties['timestamp'])) {
            unset($properties['timestamp']);
        }
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
