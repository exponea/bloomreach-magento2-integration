<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */

declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Controller\Adminhtml\ExportQueue;

use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\ExportQueueModelFactory;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Retry controller
 */
class Retry extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Bloomreach_EngagementConnector::export_queue_manage';

    /**
     * @var ExportQueue
     */
    private $exportQueueResource;

    /**
     * @var ExportQueueModelFactory
     */
    private $exportQueueModelFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param ExportQueueModelFactory $exportQueueModelFactory
     * @param ExportQueue $exportQueueResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ExportQueueModelFactory $exportQueueModelFactory,
        ExportQueue $exportQueueResource,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->exportQueueResource = $exportQueueResource;
        $this->exportQueueModelFactory = $exportQueueModelFactory;
        $this->logger = $logger;
    }

    /**
     * Update status and retries for selected item
     * - Only items with status "Error" allowed
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $entityId = (int)$this->getRequest()->getParam('id');

        if (!$entityId) {
            $this->messageManager->addErrorMessage(__('The ID is required.'));

            return $resultRedirect->setPath('*/*/');
        }

        /** @var ExportQueueModel $exportQueueModel */
        $exportQueueModel = $this->exportQueueModelFactory->create();
        $this->exportQueueResource->load($exportQueueModel, $entityId);

        if (!$exportQueueModel->getId()) {
            $this->messageManager->addErrorMessage(
                __(
                    'Unable to schedule Retry. Queue Item with ID: %entity_id does not exist.',
                    ['entity_id' => $entityId]
                )
            );

            return $resultRedirect->setPath('*/*/');
        }

        if ($exportQueueModel->getStatus() !== ExportQueueModel::STATUS_ERROR) {
            $this->messageManager->addErrorMessage(
                __('Unable to schedule Retry. Only items with status "Error" allowed.')
            );

            return $resultRedirect->setPath('*/*/');
        }

        try {
            $this->exportQueueResource->updateStatusAndRetries([$entityId]);
            $this->messageManager->addSuccessMessage(__('The Retry has been scheduled.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __(
                    'Unable to schedule Retry. Original error message: %error_message.',
                    ['error_message' => $e->getMessage()]
                )
            );
        } catch (Exception $e) {
            $this->logger->error(
                __(
                    'Unable to schedule Retry. Original error message: %error_message.',
                    ['error_message' => $e->getMessage()]
                )
            );

            $this->messageManager->addErrorMessage(__('Unable to schedule Retry. Details in Bloomreach log files.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
