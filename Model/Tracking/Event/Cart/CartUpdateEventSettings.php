<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Tracking\Event\Cart;

use Magento\Framework\App\Request\DataPersistor;

/**
 * Contains methods that allow to set/get cart update status
 */
class CartUpdateEventSettings
{
    const ADD_ACTION = 'add';

    const REMOVE_ACTION = 'remove';

    const UPDATE_ACTION = 'update';

    const EMPTY_ACTION = 'empty';

    private const EVENT_KEY = 'bloomreach_cart_updated';

    private const ACTION_KEY = 'bloomreach_cart_action';

    /**
     * @var DataPersistor
     */
    private $dataPersistor;

    /**
     * @param DataPersistor $dataPersistor
     */
    public function __construct(DataPersistor $dataPersistor)
    {
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * Sets cart updated data to the session
     *
     * @param string $action
     *
     * @return void
     */
    public function setIsCartUpdated(string $action = ''): void
    {
        $this->dataPersistor->set(self::EVENT_KEY, true);
        $this->dataPersistor->set(self::ACTION_KEY, $action);
    }

    /**
     * Checks whether is cart updated
     *
     * @return bool
     */
    public function isCartUpdated(): bool
    {
        return (bool) $this->dataPersistor->get(self::EVENT_KEY);
    }

    /**
     * Get cart action
     *
     * @return string
     */
    public function getCartAction(): string
    {
        return (string) $this->dataPersistor->get(self::ACTION_KEY);
    }

    /**
     * Clear cart updated data from session
     *
     * @return void
     */
    public function clearCartUpdatedData(): void
    {
        $this->dataPersistor->clear(self::EVENT_KEY);
        $this->dataPersistor->clear(self::ACTION_KEY);
    }
}
