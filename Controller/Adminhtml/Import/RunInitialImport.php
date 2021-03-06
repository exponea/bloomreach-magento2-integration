<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Controller\Adminhtml\Import;

use Bloomreach\EngagementConnector\Model\InitialExport\InitialEntityExportInterface;
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
 * Run initial import process
 */
class RunInitialImport extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Bloomreach_EngagementConnector::config_bloomreach_engagement';

    /**
     * Parameter to skip credential validation
     */
    private const SKIP_CREDENTIAL_VALIDATION_PARAM = 'skipCredentialValidation';

    /**
     * @var InitialEntityExportInterface
     */
    private $initialEntityExport;

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
     * @param InitialEntityExportInterface $initialEntityExport
     * @param JsonFactory $resultJsonFactory
     * @param AccessCredentialsValidator $accessCredentialsValidator
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        InitialEntityExportInterface $initialEntityExport,
        JsonFactory $resultJsonFactory,
        AccessCredentialsValidator $accessCredentialsValidator,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->initialEntityExport = $initialEntityExport;
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
            if (!$this->getRequest()->getParam(self::SKIP_CREDENTIAL_VALIDATION_PARAM, false)) {
                $this->accessCredentialsValidator->execute();
            }

            $this->initialEntityExport->execute();
            $message = __(
                'An import was successfully added to the queue. The data will soon be imported into Bloomreach.'
            );
            $error = 0;
        } catch (ValidatorException $e) {
            $message = __('Invalid credentials. See debug log for detailed error message.');
            $this->logger->error(__('Invalid credentials. Error: %1', $e->getMessage()));
        } catch (Exception $e) {
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
