<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Cron;

use Bloomreach\EngagementConnector\Model\Export\File\DirectoryProvider;
use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

/**
 * The class is responsible for cleaning old csv files
 */
class CleanCsvFilesService
{
    public const DAYS_CLEAN_CSV_FILES =
        'bloomreach_engagement/bloomreach_engagement_cron/clear_old_csv';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var File
     */
    private $driverFile;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTime $date
     * @param DirectoryList $directoryList
     * @param File $driverFile
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DateTime $date,
        DirectoryList $directoryList,
        File $driverFile,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->date = $date;
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
        $this->logger = $logger;
    }

    /**
     * Clean old csv files
     */
    public function execute(): void
    {
        $day = $this->scopeConfig->getValue(self::DAYS_CLEAN_CSV_FILES);

        if ($day > 0) {
            $timeEnd = strtotime($this->date->date()) - $day * 24 * 60 * 60;
            $directory = '/' . DirectoryProvider::DEFAULT_BASE_DIRECTORY . '/';
            $path = $this->directoryList->getPath('media') . $directory;

            if (!$this->driverFile->isExists($path)) {
                return;
            }

            $paths = $this->driverFile->readDirectoryRecursively($path);

            $this->deleteCsv($paths, $timeEnd);
        }
    }

    /**
     * Deletes old csv
     *
     * @param array $paths
     * @param int|false $timeEnd
     */
    private function deleteCsv($paths, $timeEnd): void
    {
        foreach ($paths as $filePath) {
            try {
                $pathAsArray = explode('/', $filePath);
                $fileName = array_pop($pathAsArray);

                if (strpos($fileName, '.csv') === false) {
                    continue;
                }

                $nameData = explode('-', $fileName);
                $timeFromName = array_shift($nameData);

                if (($timeFromName - $timeEnd) < 0) {
                    $this->driverFile->deleteFile($filePath);
                }
            } catch (Exception $e) {
                $this->logger->critical($e);
            }
        }
    }
}
