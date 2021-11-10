<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Block\Tracking;

use Bloomreach\EngagementConnector\Model\Tracking\Event\EventsInterface;
use Magento\Framework\View\Element\Template;

/**
 * The class is responsible for passing event data to the template
 */
class Event extends Template
{
    /**
     * Retrieve serialized JS layout configuration ready to use in template
     *
     * @return string
     */
    public function getJsLayout()
    {
        $events = $this->getData('events');

        if ($events instanceof EventsInterface) {
            $this->jsLayout = array_merge($this->jsLayout, $events->getEvents());
        }

        return parent::getJsLayout();
    }
}
