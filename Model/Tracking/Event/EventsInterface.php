<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Tracking\Event;

/**
 * The interface is using for passing events data to the Event block
 */
interface EventsInterface
{
    /**
     * Returns array with events
     *
     * @return array
     */
    public function getEvents(): array;
}
