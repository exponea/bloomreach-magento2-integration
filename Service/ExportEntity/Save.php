<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\ExportEntity;

use Bloomreach\EngagementConnector\Api\Data\ExportEntityInterface;
use Bloomreach\EngagementConnector\Api\SaveExportEntityInterface;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportEntity as ExportEntityResourceModel;
use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * The class is responsible for saving the ExportEntity entity
 */
class Save implements SaveExportEntityInterface
{
    /**
     * @var ExportEntityResourceModel
     */
    private $exportEntityResourceModel;

    /**
     * @param ExportEntityResourceModel $exportEntityResourceModel
     */
    public function __construct(ExportEntityResourceModel $exportEntityResourceModel)
    {
        $this->exportEntityResourceModel = $exportEntityResourceModel;
    }

    /**
     * Saves export entity
     *
     * @param ExportEntityInterface $exportEntity
     *
     * @return ExportEntityInterface
     * @throws CouldNotSaveException
     */
    public function execute(ExportEntityInterface $exportEntity): ExportEntityInterface
    {
        try {
            $this->exportEntityResourceModel->save($exportEntity);
        } catch (AlreadyExistsException $e) {
            return $exportEntity;
        } catch (Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save Export Entity. Entity Type: %1. Entity Id: %2. Error Message: %3',
                    $exportEntity->getEntityType(),
                    $exportEntity->getEntityId(),
                    $e->getMessage()
                )
            );
        }

        return $exportEntity;
    }
}
