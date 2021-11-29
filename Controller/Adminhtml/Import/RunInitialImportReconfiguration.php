<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Controller\Adminhtml\Import;

use Bloomreach\EngagementConnector\Model\ExportPreconfiguration\PreconfigurateEntityExport;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Start initial import reconfiguration process
 */
class RunInitialImportReconfiguration extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Bloomreach_EngagementConnector::config_bloomreach_engagement';

    /**
     * @var PreconfigurateEntityExport
     */
    private $preconfigurateEntityExport;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Context $context
     * @param PreconfigurateEntityExport $preconfigurateEntityExport
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        PreconfigurateEntityExport $preconfigurateEntityExport,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->preconfigurateEntityExport = $preconfigurateEntityExport;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Start initial import reconfiguration process
     *
     * @return Json
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $error = 0;
        $message = '';

        try {
            $preconfigurationResults = $this->preconfigurateEntityExport->execute();

            foreach ($preconfigurationResults as $preconfigurationResult) {
                if ($preconfigurationResult->hasError()) {
                    $error = 1;
                }

                $message .= $this->getMessage(
                    $preconfigurationResult->getEntityName(),
                    $preconfigurationResult->getBody()
                );
            }
        } catch (LocalizedException $e) {
            $message = $e->getMessage();
            $error = 1;
        }

        $resultJson->setData(
            [
                'message' => $message,
                'error' => $error,
            ]
        );

        return $resultJson;
    }

    /**
     * Returns preconfiguration message
     *
     * @param string $entityName
     * @param string $message
     *
     * @return string
     */
    private function getMessage(string $entityName, string $message)
    {
        return sprintf('<div><strong>%s feed dummy URL</strong>: %s</div>', $entityName, $message);
    }
}
