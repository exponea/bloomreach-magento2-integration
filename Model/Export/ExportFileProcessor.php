<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Model\Export\File\DirectoryProvider;
use Bloomreach\EngagementConnector\Model\Export\File\FileGeneratorInterface;
use Bloomreach\EngagementConnector\Model\Export\File\MediaUrlGenerator;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;

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
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var MediaUrlGenerator
     */
    private $mediaUrlGenerator;

    /**
     * @param DirectoryProvider $directoryProvider
     * @param FileGeneratorInterface $fileGenerator
     * @param SerializerInterface $jsonSerializer
     * @param MediaUrlGenerator $mediaUrlGenerator
     */
    public function __construct(
        DirectoryProvider $directoryProvider,
        FileGeneratorInterface $fileGenerator,
        SerializerInterface $jsonSerializer,
        MediaUrlGenerator $mediaUrlGenerator
    ) {
        $this->directoryProvider = $directoryProvider;
        $this->fileGenerator = $fileGenerator;
        $this->jsonSerializer = $jsonSerializer;
        $this->mediaUrlGenerator = $mediaUrlGenerator;
    }

    /**
     * Generates export file and returns file url
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return string
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    public function process(ExportQueueInterface $exportQueue): string
    {
        $fileName = $this->fileGenerator->generate(
            $this->directoryProvider->getAbsolutePath($exportQueue->getEntityType()),
            $this->jsonSerializer->unserialize($exportQueue->getBody())
        );

        return $this->mediaUrlGenerator->execute($exportQueue->getEntityType(), $fileName);
    }
}
