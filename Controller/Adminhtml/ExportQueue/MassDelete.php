<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Controller\Adminhtml\ExportQueue;

use Bloomreach\EngagementConnector\Api\DeleteExportQueueInterface;
use Bloomreach\EngagementConnector\Model\Export\Queue\AddInitialExportDataToExportQueue;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\Collection;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class to delete selected items
 */
class MassDelete extends Action implements HttpPostActionInterface
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
     * @var DeleteExportQueueInterface
     */
    private $exportQueueDeleteService;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param DeleteExportQueueInterface $exportQueueDeleteService
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        DeleteExportQueueInterface $exportQueueDeleteService
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->exportQueueDeleteService = $exportQueueDeleteService;
    }

    /**
     * Execute action
     *
     * @return Redirect
     * @throws LocalizedException|\Exception
     */
    public function execute()
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create()->addFieldToFilter(
            ExportQueueModel::API_TYPE,
            ['neq' => AddInitialExportDataToExportQueue::API_TYPE]
        );
        $collection = $this->filter->getCollection($collection);
        $exportQueueItemsDeleted = 0;
        $exportQueueItemsDeletedError = 0;

        foreach ($collection as $item) {
            try {
                $this->exportQueueDeleteService->execute((int) $item->getEntityId());

                $exportQueueItemsDeleted++;
            } catch (CouldNotDeleteException | NoSuchEntityException $e) {
                $exportQueueItemsDeletedError++;
            }
        }

        if ($exportQueueItemsDeleted) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $exportQueueItemsDeleted)
            );
        }

        if ($exportQueueItemsDeletedError) {
            $this->messageManager->addErrorMessage(
                __(
                    'A total of %1 record(s) haven\'t been deleted. Please see server logs for more details.',
                    $exportQueueItemsDeletedError
                )
            );
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
