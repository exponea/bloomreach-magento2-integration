<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Transporter;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * This class sends data to a specific endpoint, for a specific entity type, to the Bloomreach
 */
class TransporterResolver implements TransporterInterface
{
    private const DEFAULT_TRANSPORTER = 'default';

    /**
     * @var array
     */
    private $transporters;

    /**
     * @param array $transporters
     */
    public function __construct(array $transporters)
    {
        $this->transporters = $transporters;
    }

    /**
     * Sends data to the Bloomreach service
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return bool
     */
    public function send(ExportQueueInterface $exportQueue): bool
    {
        return $this->getTransporter($exportQueue->getEntityType(), $exportQueue->getApiType())->send($exportQueue);
    }

    /**
     * Get instance of transporter for specified entity type and api type
     *
     * @param string $entityType
     * @param string $apiType
     *
     * @return TransporterInterface
     * @throws ConfigurationMismatchException
     * @throws NoSuchEntityException
     */
    private function getTransporter(string $entityType, string $apiType): TransporterInterface
    {
        $transporterName = isset($this->transporters[$apiType][$entityType]) ?
            $entityType : self::DEFAULT_TRANSPORTER;

        if (!isset($this->transporters[$apiType])
            || !isset($this->transporters[$apiType][$transporterName])) {
            throw new NoSuchEntityException(
                __(
                    'There is no such transporter for such %1 API type, "%2" entity type',
                    $apiType,
                    $entityType
                )
            );
        }

        $transporter = $this->transporters[$apiType][$transporterName];

        if (!($transporter instanceof TransporterInterface)) {
            throw new ConfigurationMismatchException(
                __(
                    'Transporter "%1" must implement interface %2',
                    get_class($transporter),
                    TransporterInterface::class
                )
            );
        }

        return $transporter;
    }
}
