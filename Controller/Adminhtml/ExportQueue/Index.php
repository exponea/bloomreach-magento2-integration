<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Controller\Adminhtml\ExportQueue;

use Bloomreach\EngagementConnector\Service\Validator\BaseSettings as BaseSettingsValidator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Validation\ValidationException;

/**
 * Controller for displaying export queue table
 */
class Index extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Bloomreach_EngagementConnector::export_queue_view';
    /**
     * @var BaseSettingsValidator
     */
    private $baseSettingsValidator;

    /**
     * @param BaseSettingsValidator $baseSettingsValidator
     * @param Context $context
     */
    public function __construct(
        BaseSettingsValidator $baseSettingsValidator,
        Context $context
    ) {
        parent::__construct($context);
        $this->baseSettingsValidator = $baseSettingsValidator;
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        try {
            $this->baseSettingsValidator->validate();
        } catch (ValidationException $e) {
            $errors[] = __('Failed to open the Export Queue Dashboard page.');
            $errors[] = $e->getMessage();

            foreach ($e->getErrors() as $exception) {
                $errors[] = $exception->getMessage();
            }

            $this->messageManager->addErrorMessage(implode(' ', $errors));
            return $this->_redirect('adminhtml/system_config/edit', ['section' => 'bloomreach_engagement']);
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Bloomreach_EngagementConnector::export_queue_view');
        $resultPage->getConfig()->getTitle()->prepend(__('Export Queue'));

        return $resultPage;
    }
}
