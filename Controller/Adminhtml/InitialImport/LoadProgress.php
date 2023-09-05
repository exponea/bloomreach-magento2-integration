<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Controller\Adminhtml\InitialImport;

use Bloomreach\EngagementConnector\Block\Adminhtml\InitialImport\CurrentProgress;
use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;
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
 * The class is responsible for loading initial import progress data
 */
class LoadProgress extends Action implements HttpPostActionInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param EntityType $entityType
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        EntityType $entityType,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->entityType = $entityType;
        $this->logger = $logger;
    }

    /**
     * Loads initial import progress data
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

        if (!in_array($entityType, $this->entityType->getAllTypes())) {
            $resultJson->setHttpResponseCode(self::HTTP_BAD_REQUEST);
            $resultJson->setData(
                $this->getResponseContent(
                    false,
                    __(
                        'Entity Type with code %entity_type does not exists',
                        ['entity_type' => $entityType]
                    )
                )
            );

            return $resultJson;
        }

        try {
            $resultJson->setHttpResponseCode(self::HTTP_OK);
            $resultJson->setData(
                $this->getResponseContent(
                    true,
                    __(
                        '%entity_type initial import progress is successfully loaded',
                        [
                            'entity_type' => $this->entityType->getEntityName($entityType)
                        ]
                    ),
                    $this->getContent($entityType)
                )
            );
        } catch (LocalizedException $e) {
            $resultJson->setHttpResponseCode(self::HTTP_BAD_REQUEST);
            $resultJson->setData(
                $this->getResponseContent(
                    false,
                    __(
                        'Failed to load %entity_type initial import progress. Original error message: %error_message',
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
                    'Failed to load %entity_type initial import progress. Original error message: %error_message',
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
                        'Failed to load %entity_type initial import progress. See log for a detailed error message.',
                        ['entity_type' => $this->entityType->getEntityName($entityType)]
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
     * @param string $content
     *
     * @return array
     */
    private function getResponseContent(bool $isSuccess, Phrase $message, string $content = ''): array
    {
        return [
            'success' => $isSuccess,
            'message' => $message,
            'content' => $content
        ];
    }

    /**
     * Get content
     *
     * @param string $entityType
     *
     * @return string
     */
    private function getContent(string $entityType): string
    {
        return $this->_view->getLayout()
            ->createBlock(CurrentProgress::class)
            ->setEntityType($entityType)
            ->toHtml();
    }
}
