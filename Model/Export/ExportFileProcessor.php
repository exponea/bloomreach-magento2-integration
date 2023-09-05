<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export;

use Bloomreach\EngagementConnector\Model\Export\File\AbsoluteFilePathGenerator;
use Bloomreach\EngagementConnector\Model\Export\File\DirectoryProvider;
use Bloomreach\EngagementConnector\Model\Export\File\FileGeneratorInterface;
use Magento\Framework\Exception\FileSystemException;

/**
 * The class is responsible for creating the export file
 */
class ExportFileProcessor
{
    /**
     * @var DirectoryProvider
     */
    private $directoryProvider;

    /**
     * @var FileGeneratorInterface
     */
    private $fileGenerator;

    /**
     * @param DirectoryProvider $directoryProvider
     * @param FileGeneratorInterface $fileGenerator
     */
    public function __construct(
        DirectoryProvider $directoryProvider,
        FileGeneratorInterface $fileGenerator
    ) {
        $this->directoryProvider = $directoryProvider;
        $this->fileGenerator = $fileGenerator;
    }

    /**
     * Generates export file and returns file path
     *
     * @param array $data
     * @param string $entityType
     * @param string $fileName
     *
     * @return string
     * @throws FileSystemException
     */
    public function process(array $data, string $entityType, string $fileName): string
    {
        return $this->fileGenerator->generate(
            $this->directoryProvider->getAbsolutePath($entityType),
            $fileName,
            $data
        );
    }
}
