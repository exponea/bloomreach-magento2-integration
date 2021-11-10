<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport;

/**
 * The class is responsible for adding entities to the initial export
 */
class InitialEntityExport implements InitialEntityExportInterface
{
    /**
     * @var InitialEntityExportInterface[]
     */
    private $entitiesToExport;

    /**
     * @param array $entitiesToExport
     */
    public function __construct(array $entitiesToExport = [])
    {
        $this->entitiesToExport = $entitiesToExport;
    }

    /**
     * Adds entities to initial export
     *
     * @return void
     */
    public function execute(): void
    {
        foreach ($this->entitiesToExport as $initialEntityExport) {
            $initialEntityExport->execute();
        }
    }
}
