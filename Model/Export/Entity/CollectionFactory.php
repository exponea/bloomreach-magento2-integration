<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Entity;

use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Entity Collection factory uses to create appropriate collection class
 */
class CollectionFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string[]
     */
    private $collections;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string[] $collections
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $collections = []
    ) {
        $this->objectManager = $objectManager;
        $this->collections = $collections;
    }

    /**
     * Create instance of collection for specified entity type
     *
     * @param string $entityType
     *
     * @return AbstractDb
     * @throws NoSuchEntityException
     * @throws ConfigurationMismatchException
     */
    public function create(string $entityType)
    {
        if (!isset($this->collections[$entityType])) {
            throw new NoSuchEntityException(
                __(
                    'There is no such collection for "%1" entity type',
                    $entityType
                )
            );
        }

        $collectionClass = $this->collections[$entityType];
        $collectionEntity = $this->objectManager->create($collectionClass);

        if (!($collectionEntity instanceof AbstractDb)) {
            throw new ConfigurationMismatchException(
                __(
                    'Collection "%1" must extend %2',
                    $collectionClass,
                    AbstractDb::class
                )
            );
        }

        return $collectionEntity;
    }
}
