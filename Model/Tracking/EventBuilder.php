<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Tracking;

/**
 * The class is responsible for building event
 */
class EventBuilder
{
    /**
     * @var string
     */
    private $eventName;

    /**
     * @var array
     */
    private $eventBody;

    /**
     * @param string $eventName
     * @param array $eventBody
     */
    public function __construct(
        string $eventName = '',
        array $eventBody = []
    ) {
        $this->eventName = $eventName;
        $this->eventBody = $eventBody;
    }

    /**
     * Build event
     *
     * @return array
     */
    public function build(): array
    {
        return [
            'name' => $this->getEventName(),
            'body' => $this->getEventBody()
        ];
    }

    /**
     * Returns event name
     *
     * @return string|null
     */
    private function getEventName(): ?string
    {
        return $this->eventName;
    }

    /**
     * Returns event body
     *
     * @return array|null
     */
    private function getEventBody(): ?array
    {
        return $this->eventBody;
    }

    /**
     * Set event name
     *
     * @param string $eventName
     *
     * @return void
     */
    public function setEventName(string $eventName): void
    {
        $this->eventName = $eventName;
    }

    /**
     * Set event body
     *
     * @param array $eventBody
     *
     * @return void
     */
    public function setEventBody(array $eventBody): void
    {
        $this->eventBody = $eventBody;
    }
}
