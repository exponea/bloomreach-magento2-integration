<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Transporter\InitialExport;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Model\Export\File\MediaUrlGenerator;
use Bloomreach\EngagementConnector\Model\Export\Transporter\ResponseHandler;
use Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterInterface;
use Bloomreach\EngagementConnector\Service\Integration\StartApiImportService;
use Bloomreach\EngagementConnector\System\ImportIdResolver;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Sends data to the Bloomreach service
 */
class DefaultTransporter implements TransporterInterface
{
    /**
     * @var MediaUrlGenerator
     */
    private $mediaUrlGenerator;

    /**
     * @var StartApiImportService
     */
    private $startApiImportService;

    /**
     * @var ImportIdResolver
     */
    private $importIdResolver;

    /**
     * @var ResponseHandler
     */
    private $responseHandler;

    /**
     * @param MediaUrlGenerator $mediaUrlGenerator
     * @param StartApiImportService $startApiImportService
     * @param ImportIdResolver $importIdResolver
     * @param ResponseHandler $responseHandler
     */
    public function __construct(
        MediaUrlGenerator $mediaUrlGenerator,
        StartApiImportService $startApiImportService,
        ImportIdResolver $importIdResolver,
        ResponseHandler $responseHandler
    ) {
        $this->mediaUrlGenerator = $mediaUrlGenerator;
        $this->startApiImportService = $startApiImportService;
        $this->importIdResolver = $importIdResolver;
        $this->responseHandler = $responseHandler;
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
        $this->responseHandler->handle(
            $this->startApiImportService->execute(
                $this->importIdResolver->getImportId($exportQueue->getEntityType()),
                $this->mediaUrlGenerator->execute($exportQueue->getEntityType(), $exportQueue->getBody())
            )
        );

        return true;
    }
}
