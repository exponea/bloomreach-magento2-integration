<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\InitialExportStatus;

use Bloomreach\EngagementConnector\Api\Data\InitialExportStatusInterface;
use InvalidArgumentException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * The class is responsible for adding a new error to the InitialExportStatus entity
 */
class AddNewErrorMessage
{
    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @param SerializerInterface $jsonSerializer
     */
    public function __construct(SerializerInterface $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Adds new error
     *
     * @param InitialExportStatusInterface $initialExportStatus
     * @param string $errorMessage
     *
     * @return void
     */
    public function execute(InitialExportStatusInterface $initialExportStatus, string $errorMessage): void
    {
        try {
            $errors = $initialExportStatus->getErrors() ?
                $this->jsonSerializer->unserialize($initialExportStatus->getErrors()) : [];
            $errors[] = $errorMessage;
            $initialExportStatus->setErrors($this->jsonSerializer->serialize($errors));
        } catch (InvalidArgumentException $e) {
            return;
        }
    }
}
