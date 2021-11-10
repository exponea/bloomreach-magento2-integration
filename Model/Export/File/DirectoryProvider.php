<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;

/**
 * The class is responsible for creating the export directories for each entity type
 */
class DirectoryProvider
{
    public const DEFAULT_BASE_DIRECTORY = 'bloomreach';

    private const DEFAULT_ENTITY_DIRECTORY = 'entity';

    /**
     * @var string
     */
    private $baseDirectory;

    /**
     * @var array
     */
    private $entityDirectories;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @param Filesystem $fileSystem
     * @param string $baseDirectory
     * @param array $entityDirectories
     */
    public function __construct(
        FileSystem $fileSystem,
        string $baseDirectory = self::DEFAULT_BASE_DIRECTORY,
        array $entityDirectories = []
    ) {
        $this->fileSystem = $fileSystem;
        $this->baseDirectory = $baseDirectory;
        $this->entityDirectories = $entityDirectories;
    }

    /**
     * Get absolute path
     *
     * @param string $entityType
     *
     * @return string
     * @throws FileSystemException
     */
    public function getAbsolutePath(string $entityType): string
    {
        return $this->fileSystem
                ->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath() . $this->getDirPath($entityType);
    }

    /**
     * Get dir path
     *
     * @param string $entityType
     *
     * @return string
     * @throws FileSystemException
     */
    public function getDirPath(string $entityType): string
    {
        $path = $this->baseDirectory . DIRECTORY_SEPARATOR . $this->getDirName($entityType);
        $this->createDirectory($path);

        return $path;
    }

    /**
     * Returns dir name for particular entity types
     *
     * @param string $entityType
     *
     * @return string
     */
    private function getDirName(string $entityType): string
    {
        return $this->entityDirectories[$entityType] ?? self::DEFAULT_ENTITY_DIRECTORY;
    }

    /**
     * Create a directory
     *
     * @param string $path
     *
     * @return void
     * @throws FileSystemException
     */
    private function createDirectory(string $path): void
    {
        $this->fileSystem
            ->getDirectoryWrite(DirectoryList::MEDIA)
            ->create($path);
    }
}
