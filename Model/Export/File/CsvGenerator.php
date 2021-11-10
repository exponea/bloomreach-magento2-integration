<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\File;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\File\Csv;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * This class is responsible for CSV file generation
 */
class CsvGenerator implements FileGeneratorInterface
{
    /**
     * @var FileNameGenerator
     */
    private $fileNameGenerator;

    /**
     * @var Csv
     */
    private $csv;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @param Csv $csv
     * @param FileNameGenerator $fileNameGenerator
     * @param SerializerInterface $jsonSerializer
     */
    public function __construct(
        Csv $csv,
        FileNameGenerator $fileNameGenerator,
        SerializerInterface $jsonSerializer
    ) {
        $this->csv = $csv;
        $this->fileNameGenerator = $fileNameGenerator;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Generate csv file
     *
     * @param string $absoluteDirPath
     * @param array $data
     *
     * @return string
     * @throws FileSystemException
     */
    public function generate(string $absoluteDirPath, array $data): string
    {
        $fileName = $this->getFileName();
        $data = $this->prepareToExport($data);
        array_unshift($data, $this->getHeaders($data));
        $this->csv
            ->setDelimiter(',')
            ->setEnclosure('"')
            ->appendData($this->getFullFilePath($absoluteDirPath, $fileName), $data, 'a');

        return $fileName;
    }

    /**
     * Get file name
     *
     * @return string
     */
    private function getFileName(): string
    {
        return $this->fileNameGenerator->execute() . '.csv';
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
