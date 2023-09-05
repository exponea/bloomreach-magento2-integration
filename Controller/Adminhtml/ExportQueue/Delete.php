<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Controller\Adminhtml\ExportQueue;

use Bloomreach\EngagementConnector\Api\DeleteExportQueueInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Delete entity id action
 */
class Delete extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Bloomreach_EngagementConnector::export_queue_manage';

    /**
     * @var DeleteExportQueueInterface
     */
    private $exportQueueDeleteService;

    /**
     * @param Context $context
     * @param DeleteExportQueueInterface $exportQueueDeleteService
     */
    public function __construct(
        Context $context,
        DeleteExportQueueInterface $exportQueueDeleteService
    ) {
        parent::__construct($context);
        $this->exportQueueDeleteService = $exportQueueDeleteService;
    }

    /**
     * Delete Action
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

        try {
            $this->exportQueueDeleteService->execute($entityId);
            $this->messageManager->addSuccessMessage(__('The Item has been deleted.'));
            $this->_redirect('*/*/');
        } catch (CouldNotDeleteException | NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }
}
