<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */

declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Controller\Adminhtml\ExportQueue;

use Bloomreach\EngagementConnector\Model\Export\Queue\AddInitialExportDataToExportQueue;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\Collection;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\CollectionFactory;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

/**
 * Controller for mass retry
 */
class MassRetry extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Bloomreach_EngagementConnector::export_queue_manage';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
    }

    /**
     * Update status and retries for selected items
     * - Only items with status "Error" allowed
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter(
                ExportQueueModel::API_TYPE,
                ['neq' => AddInitialExportDataToExportQueue::API_TYPE]
            )
            ->addFieldToFilter(
                ExportQueueModel::STATUS,
                ExportQueueModel::STATUS_ERROR
            );

        try {
            $totalUpdated = 0;
            $collection = $this->filter->getCollection($collection);
            $entityIds = $collection->getAllIds();

            if ($entityIds) {
                $totalUpdated = $collection->getResource()->updateStatusAndRetries($entityIds);
            }

            if ($totalUpdated > 0) {
                $this->messageManager->addSuccessMessage(
                    __(
                        'Retry has been scheduled for %total_updated item(s). '
                        . 'Items will be resubmitted during the next cron iteration',
                        ['total_updated' => $totalUpdated]
                    )
                );

                return $resultRedirect->setPath('*/*/');
            }

            $this->messageManager->addErrorMessage(
                __('Unable to schedule Retry. Only items with status "Error" can be resent.')
            );
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
