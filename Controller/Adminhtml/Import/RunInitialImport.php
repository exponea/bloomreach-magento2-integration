<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Controller\Adminhtml\Import;

use Bloomreach\EngagementConnector\Model\InitialExport\InitialEntityExportInterface;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

/**
 * Run initial import process
 */
class RunInitialImport extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Bloomreach_EngagementConnector::config_bloomreach_engagement';

    /**
     * @var InitialEntityExportInterface
     */
    private $initialEntityExport;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param InitialEntityExportInterface $initialEntityExport
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        InitialEntityExportInterface $initialEntityExport,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->initialEntityExport = $initialEntityExport;
        $this->logger = $logger;
    }

    /**
     * Run initial import
     *
     * @return Json
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $error = 0;

        try {
            $this->initialEntityExport->execute();
            $message = __(
                'An import was successfully added to the queue. The data will soon be imported into Bloomreach.'
            );
        } catch (Exception $e) {
            $error = 1;
            $this->logger->error(
                __(
                    'An error occurred while running initial Import. Error: %1',
                    $e->getMessage()
                )
            );
            $message = __('An error occurred while running initial Import. Contact technical support.');
        }

        $resultJson->setData(
            [
                'message' => $message,
                'error' => $error,
            ]
        );

        return $resultJson;
    }
}
