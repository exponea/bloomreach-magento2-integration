<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\File;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * This class is responsible for CSV file generation
 */
class CsvGenerator implements FileGeneratorInterface
{
    private const FILENAME_EXTENSION = 'csv';

    /**
     * @var Csv
     */
    private $csv;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var FileDriver
     */
    private $fileDriver;

    /**
     * @param Csv $csv
     * @param SerializerInterface $jsonSerializer
     * @param FileDriver $fileDriver
     */
    public function __construct(
        Csv $csv,
        SerializerInterface $jsonSerializer,
        FileDriver $fileDriver
    ) {
        $this->csv = $csv;
        $this->jsonSerializer = $jsonSerializer;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Generate csv file
     *
     * @param string $absoluteDirPath
     * @param string $fileName
     * @param array $data
     *
     * @return string
     * @throws FileSystemException
     */
    public function generate(string $absoluteDirPath, string $fileName, array $data): string
    {
        $fullFileName = $this->getFullFileName($fileName);
        $absoluteFilePath = $this->getFullFilePath($absoluteDirPath, $fullFileName);
        $data = $this->prepareToExport($data);

        if (!$this->fileDriver->isExists($absoluteFilePath) && $this->hasHeaders($data)) {
            array_unshift($data, $this->getHeaders($data));
        }

        $this->csv
            ->setDelimiter(',')
            ->setEnclosure('"')
            ->appendData($absoluteFilePath, $data, 'a');

        return $fullFileName;
    }

    /**
     * Get full file name
     *
     * @param string $fileName
     *
     * @return string
     */
    private function getFullFileName(string $fileName): string
    {
        return sprintf('%s.%s', $fileName, self::FILENAME_EXTENSION);
    }

    /**
     * Prepare data to the export
     *
     * @param array $data
     *
     * @return array
     */
    private function prepareToExport(array $data): array
    {
        if (array_is_list($data) && !is_array(current($data))) {
            return [array_map([$this, 'serialize'], $data)];
        }

        $data = count($data) > 0 && !is_array(current($data)) ? [$data] : $data;

        foreach ($data as $key => $item) {
            $data[$key] = array_map([$this, 'serialize'], $item);
        }

        return $data;
    }

    /**
     * Get Headers
     *
     * @param array $data
     *
     * @return array
     */
    private function getHeaders(array $data): array
    {
        return array_keys(current($data));
    }

    /**
     * Checks whether data has headers
     *
     * @param array $data
     *
     * @return bool
     */
    private function hasHeaders(array $data): bool
    {
        return !array_is_list(current($data));
    }

    /**
     * Get full file path
     *
     * @param string $absolutePath
     * @param string $fileName
     *
     * @return string
     */
    private function getFullFilePath(string $absolutePath, string $fileName): string
    {
        return rtrim($absolutePath, '/') . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * Serialize data
     *
     * @param array|string|int $data
     *
     * @return bool|string
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function serialize($data)
    {
        if (is_array($data)) {
            return $this->jsonSerializer->serialize($data);
        }

        return $data;
    }
}
