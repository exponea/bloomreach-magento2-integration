<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\DataMapper;

use Bloomreach\EngagementConnector\Model\DataMapping\Event\RegisteredGenerator;
use Magento\Framework\DataObject;

/**
 * The class is responsible for mapping registered fields
 */
class RegisteredMapper
{
    /**
     * @var RegisteredGenerator
     */
    private $registeredGenerator;

    /**
     * @param RegisteredGenerator $registeredGenerator
     */
    public function __construct(RegisteredGenerator $registeredGenerator)
    {
        $this->registeredGenerator = $registeredGenerator;
    }
    /**
     * Maps Registered
     *
     * @param DataObject $data
     *
     * @return DataObject
     */
    public function map(DataObject $data): DataObject
    {
        foreach ($this->registeredGenerator->getRegisteredMapping() as $fromId => $toId) {
            if (!$data->hasData($fromId) || $fromId === $toId) {
                continue;
            }

            $data->setData($toId, $data->getData($fromId));
            $data->unsetData($fromId);
        }

        return $data;
    }
}
