<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Setup\Service;

use Bloomreach\EngagementConnector\Model\Export\File\DirectoryProvider;
use Exception;
use Magento\Framework\App\Filesystem\DirectoryList as AppDirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File as DriverFile;
use Psr\Log\LoggerInterface;

/**
 * Remove data files
 */
class RemoveFiles
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var DriverFile
     */
    private $driverFile;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DriverFile $driverFile
     * @param DirectoryList $directoryList
     * @param LoggerInterface $logger
     */
    public function __construct(
        DriverFile $driverFile,
        DirectoryList $directoryList,
        LoggerInterface $logger
    ) {
        $this->driverFile = $driverFile;
        $this->directoryList = $directoryList;
        $this->logger = $logger;
    }

    /**
     * Removing files
     *
     * @return void
     * @throws FileSystemException
     */
    public function execute(): void
    {
        $this->deleteImportFiles();
        $this->deleteLogFiles();
    }

    /**
     * Delete log files
     *
     * @return void
     * @throws FileSystemException
     */
    private function deleteLogFiles(): void
    {
        $this->deleteDirectoryRecursively(
            $this->directoryList->getPath(AppDirectoryList::LOG)
            . DIRECTORY_SEPARATOR
            . DirectoryProvider::DEFAULT_BASE_DIRECTORY
        );
    }

    /**
     * Delete import files
     *
     * @return void
     * @throws FileSystemException
     */
    private function deleteImportFiles(): void
    {
        $this->deleteDirectoryRecursively(
            $this->directoryList->getPath(AppDirectoryList::MEDIA)
            . DIRECTORY_SEPARATOR
            . DirectoryProvider::DEFAULT_BASE_DIRECTORY
        );
    }

    /**
     * Delete directory
     *
     * @param string $basePath
     *
     * @return void
     * @throws FileSystemException
     */
    private function deleteDirectoryRecursively(string $basePath): void
    {
        if (!$this->driverFile->isExists($basePath)) {
            return;
        }
        $paths = $this->driverFile->readDirectoryRecursively($basePath);
        if (count($paths)) {
            foreach ($paths as $filePath) {
                try {
                    if ($this->driverFile->isExists($filePath)) {
                        if ($this->driverFile->isFile($filePath)) {
                            $this->driverFile->deleteFile($filePath);
                        } elseif ($this->driverFile->isDirectory($filePath)) {
                            $this->driverFile->deleteDirectory($filePath);
                        }
                    }
                } catch (Exception $e) {
                    $this->logger->critical($e);
                }
            }
        }
        try {
            $this->driverFile->deleteDirectory($basePath);
        } catch (Exception $e) {
            $this->logger->critical($e);
        }
    }
}
