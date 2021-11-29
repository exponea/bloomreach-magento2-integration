<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Tracking\Event\Cart;

use Bloomreach\EngagementConnector\Model\Tracking\Event\EventsInterface;

/**
 * The class is responsible for returning the array with the events to be sent after updating the cart
 */
class CartUpdateEventsCollector
{
    /**
     * @var EventsInterface[]
     */
    private $eventsList;

    /**
     * @param array $eventsList
     */
    public function __construct(array $eventsList = [])
    {
        $this->eventsList = $eventsList;
    }

    /**
     * Returns events
     *
     * @return array
     */
    public function getEvents(): array
    {
        $eventsList = [];

        foreach ($this->eventsList as $event) {
            $eventsList[] = $event->getEvents();
        }

        return $eventsList;
    }
}
