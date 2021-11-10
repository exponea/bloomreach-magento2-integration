<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\File;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManager;

/**
 * The class is responsible for generation a url for file
 */
class MediaUrlGenerator
{
    /**
     * @var DirectoryProvider
     */
    private $directoryProvider;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @param DirectoryProvider $directoryProvider
     * @param StoreManager $storeManager
     */
    public function __construct(
        DirectoryProvider $directoryProvider,
        StoreManager $storeManager
    ) {
        $this->directoryProvider = $directoryProvider;
        $this->storeManager = $storeManager;
    }

    /**
     * Get file url
     *
     * @param string $entityType
     * @param string $fileName
     *
     * @return string
     *
     * @throws NoSuchEntityException
     * @throws FileSystemException
     */
    public function execute(string $entityType, string $fileName): string
    {
        $store = $this->getStore();

        return $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) .
            $this->directoryProvider->getDirPath($entityType) . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * Returns store
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    private function getStore(): StoreInterface
    {
        if (null === $this->store) {
            $this->store = $this->storeManager->getStore();
        }

        return $this->store;
    }
}
