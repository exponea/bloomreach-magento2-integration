<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Transporter\InitialExport;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Model\Export\ExportFileProcessor;
use Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterInterface;
use Bloomreach\EngagementConnector\Service\Integration\ImportIdResolver;
use Bloomreach\EngagementConnector\Service\Integration\StartApiImportService;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Sends data to the Bloomreach service
 */
class DefaultTransporter implements TransporterInterface
{
    /**
     * @var ExportFileProcessor
     */
    private $exportFileProcessor;

    /**
     * @var StartApiImportService
     */
    private $startApiImportService;

    /**
     * @var ImportIdResolver
     */
    private $importIdResolver;

    /**
     * @param ExportFileProcessor $exportFileProcessor
     * @param StartApiImportService $startApiImportService
     * @param ImportIdResolver $importIdResolver
     */
    public function __construct(
        ExportFileProcessor $exportFileProcessor,
        StartApiImportService $startApiImportService,
        ImportIdResolver $importIdResolver
    ) {
        $this->exportFileProcessor = $exportFileProcessor;
        $this->startApiImportService = $startApiImportService;
        $this->importIdResolver = $importIdResolver;
    }

    /**
     * Sends data to the Bloomreach service
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return bool
     *
     * @throws FileSystemException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function send(ExportQueueInterface $exportQueue): bool
    {
        $response = $this->startApiImportService->execute(
            $this->importIdResolver->getImportId($exportQueue->getEntityType()),
            $this->exportFileProcessor->process($exportQueue)
        );

        if ((int) $response->getStatusCode() !== 200) {
            throw new LocalizedException(
                __($response->getReasonPhrase())
            );
        }

        return true;
    }
}
