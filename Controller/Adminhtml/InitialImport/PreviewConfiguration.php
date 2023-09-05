<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Controller\Adminhtml\InitialImport;

use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Source\SourceGenerator;
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
 * The class is responsible for previewing import configuration
 */
class PreviewConfiguration extends Action implements HttpPostActionInterface
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
     * @var SourceGenerator
     */
    private $sourceGenerator;

    /**
     * @var EntityType
     */
    private $entityType;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param SourceGenerator $sourceGenerator
     * @param EntityType $entityType
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        SourceGenerator $sourceGenerator,
        EntityType $entityType,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->sourceGenerator = $sourceGenerator;
        $this->entityType = $entityType;
        $this->logger = $logger;
    }

    /**
     * Generates Preview Import Configuration
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
            $source = $this->sourceGenerator->generate($entityType);
            $resultJson->setHttpResponseCode(self::HTTP_OK);
            $resultJson->setData(
                $this->getResponseContent(
                    true,
                    __(
                        '%entity_type initial import configuration successfully loaded',
                        [
                            'entity_type' => $this->entityType->getEntityName($entityType)
                        ]
                    ),
                    $source->getFileUrl()
                )
            );
        } catch (LocalizedException $e) {
            $message = __(
                'Failed to configure %entity_type initial import. Original error message: %error_message',
                [
                    'entity_type' => $this->entityType->getEntityName($entityType),
                    'error_message' => $e->getMessage()
                ]
            );
            $resultJson->setHttpResponseCode(self::HTTP_BAD_REQUEST);
            $resultJson->setData(
                $this->getResponseContent(
                    false,
                    $message
                )
            );
        } catch (Exception $e) {
            $this->logger->error(
                __(
                    'Failed to configure %entity_type initial import. Original error message: %error_message',
                    [
                        'entity_type' => $this->entityType->getEntityName($entityType),
                        'error_message' => $e->getMessage()
                    ]
                )
            );
            $responseMessage = __(
                'Failed to configure %entity_type initial import. See log for a detailed error message.',
                [
                    'entity_type' => $this->entityType->getEntityName($entityType)
                ]
            );
            $resultJson->setHttpResponseCode(self::HTTP_INTERNAL_ERROR);
            $resultJson->setData(
                $this->getResponseContent(
                    false,
                    $responseMessage
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
     * @param string $fileUrl
     *
     * @return array
     */
    private function getResponseContent(bool $isSuccess, Phrase $message, string $fileUrl = ''): array
    {
        return [
            'success' => $isSuccess,
            'preview_content' => [
                'file_url' => $fileUrl
            ],
            'message' => $message
        ];
    }
}
