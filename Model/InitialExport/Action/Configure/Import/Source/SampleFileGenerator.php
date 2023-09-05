<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Source;

use Bloomreach\EngagementConnector\Model\Export\ExportFileProcessor;
use Bloomreach\EngagementConnector\Model\Export\File\FileNameGenerator;
use Bloomreach\EngagementConnector\Model\Export\File\MediaUrlGenerator;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * The class is responsible for generating sample file
 */
class SampleFileGenerator
{
    /**
     * @var ExportFileProcessor
     */
    private $exportFileProcessor;

    /**
     * @var FileNameGenerator
     */
    private $fileNameGenerator;

    /**
     * @var MediaUrlGenerator
     */
    private $mediaUrlGenerator;

    /**
     * @param ExportFileProcessor $exportFileProcessor
     * @param FileNameGenerator $fileNameGenerator
     * @param MediaUrlGenerator $mediaUrlGenerator
     */
    public function __construct(
        ExportFileProcessor $exportFileProcessor,
        FileNameGenerator $fileNameGenerator,
        MediaUrlGenerator $mediaUrlGenerator
    ) {
        $this->exportFileProcessor = $exportFileProcessor;
        $this->fileNameGenerator = $fileNameGenerator;
        $this->mediaUrlGenerator = $mediaUrlGenerator;
    }

    /**
     * Generates sample file and returns file url
     *
     * @param array $data
     * @param string $entityType
     *
     * @return string
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    public function execute(array $data, string $entityType): string
    {
        return $this->mediaUrlGenerator->execute(
            $entityType,
            $this->exportFileProcessor->process($data, $entityType, $this->fileNameGenerator->execute())
        );
    }
}
