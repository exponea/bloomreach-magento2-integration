<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Model\Export\File;

/**
 * Generate export file
 */
interface FileGeneratorInterface
{
    /**
     * Generate export file
     *
     * @param string $absoluteDirPath
     * @param string $fileName
     * @param array $data
     *
     * @return string
     */
    public function generate(string $absoluteDirPath, string $fileName, array $data): string;
}
