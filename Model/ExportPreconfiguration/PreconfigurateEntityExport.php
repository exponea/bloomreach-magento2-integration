<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\ExportPreconfiguration;

use Magento\Framework\Exception\LocalizedException;

/**
 * The class is responsible for preconfiguration export entities
 */
class PreconfigurateEntityExport
{
    /**
     * @var PreconfigurateEntityExportInterface[]
     */
    private $entitiesToPreconfigurate;

    /**
     * @param array $entitiesToPreconfigurate
     */
    public function __construct(array $entitiesToPreconfigurate = [])
    {
        $this->entitiesToPreconfigurate = $entitiesToPreconfigurate;
    }

    /**
     * Returns export preconfiguration result for each entity
     *
     * @return PreconfigurationResultInterface[]
     * @throws LocalizedException
     */
    public function execute(): array
    {
        $result = [];

        foreach ($this->entitiesToPreconfigurate as $item) {
            $result[] = $item->execute();
        }

        if (!$result) {
            throw new LocalizedException(
                __('There are no entities for preconfigurate')
            );
        }

        return $result;
    }
}
