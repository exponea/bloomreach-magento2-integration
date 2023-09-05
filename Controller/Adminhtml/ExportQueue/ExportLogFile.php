<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Controller\Adminhtml\ExportQueue;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Psr\Log\LoggerInterface;

/**
 * Export files
 */
class ExportLogFile extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Bloomreach_EngagementConnector::export_queue_manage';

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DirectoryList
     */
    private $directory;

    /**
     * @var File
     */
    private $fileDriver;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param File $fileDriver
     * @param LoggerInterface $logger
     * @param DirectoryList $directory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        File $fileDriver,
        LoggerInterface $logger,
        DirectoryList $directory
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->fileFactory = $fileFactory;
        $this->directory = $directory;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Download file from log directory
     *
     * @return ResultInterface|ResponseInterface
     * @throws FileSystemException
     */
    public function execute()
    {
        $fileName = $this->getRequest()->getParam('fileName');
        $filePath = $this->directory->getPath(DirectoryList::LOG) . DIRECTORY_SEPARATOR . trim($fileName, '/');

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->fileDriver->isExists($filePath)) {
            $this->messageManager->addErrorMessage('File doesn\'t exist');
            return $resultRedirect->setPath('*/*/index');
        }

        try {
            return $this->fileFactory->create(
                $fileName,
                [
                    'type' => 'filename',
                    'value' => $filePath,
                ],
                DirectoryList::LOG
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __(
                    'And error occurred while downloading a %file_name log file.
                     Original error message: %error_message',
                    [
                        'file_name' => $fileName,
                        'error_message' => $e->getMessage()
                    ]
                )
            );
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(
                __('And error occurred while downloading a %file_name log file.
                 See log for a detailed error message')
            );
            $this->logger->error(
                __(
                    'And error occurred while downloading a %file_name log file.
                     Original error message: %error_message',
                    [
                        'file_name' => $fileName,
                        'error_message' => $e->getMessage()
                    ]
                )
            );
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
