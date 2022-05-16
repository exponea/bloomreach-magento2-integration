<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Controller\Adminhtml\Validator;

use Bloomreach\EngagementConnector\Service\Validator\AccessCredentials as AccessCredentialsValidator;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\ValidatorException;
use Psr\Log\LoggerInterface;

/**
 * Validate Access Credentials
 */
class AccessCredentials extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Bloomreach_EngagementConnector::config_bloomreach_engagement';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var AccessCredentialsValidator
     */
    private $accessCredentialsValidator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param AccessCredentialsValidator $accessCredentialsValidator
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        AccessCredentialsValidator $accessCredentialsValidator,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->accessCredentialsValidator = $accessCredentialsValidator;
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
        $error = 1;

        try {
            $this->accessCredentialsValidator->execute();
            $message = __('Credentials are correct.');
            $error = 0;
        } catch (ValidatorException $e) {
            $message = __('Invalid credentials. See debug log for detailed error message.');
            $this->logger->error(__('Invalid credentials. Error: %1', $e->getMessage()));
        } catch (Exception $e) {
            $this->logger->error(
                __(
                    'An error occurred while validating access credentials. Error: %1',
                    $e->getMessage()
                )
            );
            $message = __('An error occurred while validating access credentials. Contact technical support.');
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
