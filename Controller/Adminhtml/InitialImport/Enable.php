<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Controller\Adminhtml\InitialImport;

use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Enable as EnableInitialExport;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Psr\Log\LoggerInterface;

/**
 * The class is responsible for enabling import for entity type
 */
class Enable extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Bloomreach_EngagementConnector::initial_import_manage';

    private const HTTP_OK = 200;

    private const HTTP_INTERNAL_ERROR = 500;

    private const HTTP_BAD_REQUEST = 400;

    /**
     * @var EntityType
     */
    private $entityType;

    /**
     * @var EnableInitialExport
     */
    private $enableInitialExport;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param EntityType $entityType
     * @param EnableInitialExport $enableInitialExport
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        EntityType $entityType,
        EnableInitialExport $enableInitialExport,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->entityType = $entityType;
        $this->enableInitialExport = $enableInitialExport;
        $this->logger = $logger;
    }

    /**
     * Enables import
     *
     * @return Json
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $entityType = $this->getRequest()->getParam('entity_type');

        if (!$entityType) {
            $resultJson->setHttpResponseCode(self::HTTP_BAD_REQUEST);
            $resultJson->setData($this->getResponseContent(false, __('entity_type is required')));

            return $resultJson;
        }

        try {
            $this->enableInitialExport->execute($entityType);
            $message = __(
                '%entity_type initial import is successfully enabled',
                [
                    'entity_type' => $this->entityType->getEntityName($entityType)
                ]
            );
            $resultJson->setHttpResponseCode(self::HTTP_OK);
            $resultJson->setData(
                $this->getResponseContent(
                    true,
                    $message
                )
            );
            $this->messageManager->addSuccessMessage($message);
        } catch (LocalizedException $e) {
            $resultJson->setHttpResponseCode(self::HTTP_BAD_REQUEST);
            $resultJson->setData(
                $this->getResponseContent(
                    false,
                    __(
                        'Failed to enable %entity_type initial import. Original error message: %error_message',
                        [
                            'entity_type' => $this->entityType->getEntityName($entityType),
                            'error_message' => $e->getMessage()
                        ]
                    )
                )
            );
        } catch (Exception $e) {
            $this->logger->error(
                __(
                    'Failed to enable %entity_type initial import. Original error message: %error_message',
                    [
                        'entity_type' => $this->entityType->getEntityName($entityType),
                        'error_message' => $e->getMessage()
                    ]
                )
            );
            $resultJson->setHttpResponseCode(self::HTTP_INTERNAL_ERROR);
            $resultJson->setData(
                $this->getResponseContent(
                    false,
                    __(
                        'Failed to enable %entity_type initial import. See log for a detailed error message.',
                        [
                            'entity_type' => $this->entityType->getEntityName($entityType)
                        ]
                    )
                )
            );
        }

        return $resultJson;
    }

    /**
     * Get response content
     *
     * @param bool $isSuccess
     * @param Phrase $message
     *
     * @return array
     */
    private function getResponseContent(bool $isSuccess, Phrase $message): array
    {
        return [
            'success' => $isSuccess,
            'message' => $message
        ];
    }
}
