<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\ExportPreconfiguration;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapperResolver;
use Bloomreach\EngagementConnector\Model\Export\File\DirectoryProvider;
use Bloomreach\EngagementConnector\Model\Export\File\FileGeneratorInterface;
use Bloomreach\EngagementConnector\Model\Export\File\MediaUrlGenerator;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Model\AbstractModel;

/**
 * The class is responsible for generating dummy file
 */
class DummyFileGenerator
{
    /**
     * @var DataMapperResolver
     */
    private $dataMapperResolver;

    /**
     * @var DirectoryProvider
     */
    private $directoryProvider;

    /**
     * @var FileGeneratorInterface
     */
    private $fileGenerator;

    /**
     * @var MediaUrlGenerator
     */
    private $mediaUrlGenerator;

    /**
     * @param DataMapperResolver $dataMapperResolver
     * @param DirectoryProvider $directoryProvider
     * @param FileGeneratorInterface $fileGenerator
     * @param MediaUrlGenerator $mediaUrlGenerator
     */
    public function __construct(
        DataMapperResolver $dataMapperResolver,
        DirectoryProvider $directoryProvider,
        FileGeneratorInterface $fileGenerator,
        MediaUrlGenerator $mediaUrlGenerator
    ) {
        $this->dataMapperResolver = $dataMapperResolver;
        $this->directoryProvider = $directoryProvider;
        $this->fileGenerator = $fileGenerator;
        $this->mediaUrlGenerator = $mediaUrlGenerator;
    }

    /**
     * Generates dummy file and returns file url
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $entityType
     *
     * @return string
     * @throws ConfigurationMismatchException
     * @throws FileSystemException
     * @throws NoSuchEntityException
     * @throws NotFoundException
     */
    public function execute($entity, string $entityType): string
    {
        $data = $this->dataMapperResolver->map($entity, $entityType)->toArray();
        $fileName = $this->fileGenerator->generate(
            $this->directoryProvider->getAbsolutePath($entityType),
            [$data]
        );

        return $this->mediaUrlGenerator->execute($entityType, $fileName);
    }
}
