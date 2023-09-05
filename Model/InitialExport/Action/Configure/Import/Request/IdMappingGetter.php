<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Request;

use Bloomreach\EngagementConnector\Model\DataMapping\Event\RegisteredGenerator;

/**
 * The class is responsible for preparing id mapping
 */
class IdMappingGetter
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
     * Returns id mapping
     *
     * @return array
     */
    public function execute(): array
    {
        $result = [];

        foreach ($this->registeredGenerator->getRegisteredMapping() as $bloomreachId) {
            $result[] = [
                'from_column' => $bloomreachId,
                'to_id' => $bloomreachId
            ];
        }

        return $result;
    }
}
