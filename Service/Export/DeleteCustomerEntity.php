<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Export;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Customer as CustomerDataMapper;
use Bloomreach\EngagementConnector\Model\DataMapping\Event\RegisteredGenerator;
use Bloomreach\EngagementConnector\Model\Export\Queue\AddDeleteActionToExportQueue;
use Magento\Customer\Model\Customer;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * The class is responsible for adding delete customer action to the export queue
 */
class DeleteCustomerEntity
{
    /**
     * @var AddDeleteActionToExportQueue
     */
    private $addDeleteActionToExportQueue;

    /**
     * @var RegisteredGenerator
     */
    private $registeredGenerator;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param AddDeleteActionToExportQueue $addDeleteActionToExportQueue
     * @param RegisteredGenerator $registeredGenerator
     * @param SerializerInterface $jsonSerializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        AddDeleteActionToExportQueue $addDeleteActionToExportQueue,
        RegisteredGenerator $registeredGenerator,
        SerializerInterface $jsonSerializer,
        LoggerInterface $logger
    ) {
        $this->addDeleteActionToExportQueue = $addDeleteActionToExportQueue;
        $this->registeredGenerator = $registeredGenerator;
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $logger;
    }

    /**
     * Add delete action for customer entity type
     *
     * @param Customer $customer
     *
     * @return void
     */
    public function execute(Customer $customer): void
    {
        try {
            $this->addDeleteActionToExportQueue->execute(
                CustomerDataMapper::ENTITY_TYPE,
                $this->jsonSerializer->serialize($this->buildBody($customer))
            );
        } catch (\Exception $e) {
            $this->logger->error(
                __(
                    'An error occurred while adding delete action for entity with ID: %entityId'
                    . ' to the export queue for %entityType entity type. Error: %errorMessage',
                    [
                        'entityId' => $customer->getEntityId(),
                        'entityType' => CustomerDataMapper::ENTITY_TYPE,
                        'errorMessage' => $e->getMessage()
                    ]
                )
            );
        }
    }

    /**
     * Build export queue body
     *
     * @param Customer $customer
     *
     * @return array[]
     */
    private function buildBody(Customer $customer): array
    {
        return [
            'customers' => [
                [
                    'customer_ids' => $this->registeredGenerator->generate(
                        $customer->getEmail(),
                        $customer->getEntityId() ? (int) $customer->getEntityId() : null
                    )
                ]
            ]
        ];
    }
}
