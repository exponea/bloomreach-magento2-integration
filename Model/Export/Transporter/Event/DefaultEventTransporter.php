<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Transporter\Event;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Model\Export\Queue\Batch\Command\Data\Builder\BuilderInterface as EventBuilder;
use Bloomreach\EngagementConnector\Model\Export\Transporter\ResponseHandler;
use Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterInterface;
use Bloomreach\EngagementConnector\Service\Integration\SendEventRequest;
use Magento\Framework\Exception\LocalizedException;

/**
 * Sends event data to the Bloomreach service
 */
class DefaultEventTransporter implements TransporterInterface
{
    /**
     * @var SendEventRequest
     */
    private $sendEventRequest;

    /**
     * @var EventBuilder
     */
    private $eventBuilder;

    /**
     * @var ResponseHandler
     */
    private $responseHandler;

    /**
     * @param SendEventRequest $sendEventRequest
     * @param EventBuilder $eventBuilder
     * @param ResponseHandler $responseHandler
     */
    public function __construct(
        SendEventRequest $sendEventRequest,
        EventBuilder $eventBuilder,
        ResponseHandler $responseHandler
    ) {
        $this->sendEventRequest = $sendEventRequest;
        $this->eventBuilder = $eventBuilder;
        $this->responseHandler = $responseHandler;
    }

    /**
     * Sends event data to the Bloomreach service
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return bool
     * @throws LocalizedException
     */
    public function send(ExportQueueInterface $exportQueue): bool
    {
        $this->responseHandler->handle($this->sendEventRequest->execute($this->eventBuilder->build($exportQueue)));

        return true;
    }
}
