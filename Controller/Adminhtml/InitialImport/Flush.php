<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Controller\Adminhtml\InitialImport;

use Bloomreach\EngagementConnector\Exception\AuthenticationException;
use Bloomreach\EngagementConnector\Exception\AuthorizationException;
use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Flush as FlushAction;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Validation\ValidationException;
use Psr\Log\LoggerInterface;

/**
 * The class is responsible for flushing the initial import
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Flush extends Action implements HttpPostActionInterface
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
     * @var FlushAction
     */
    private $flushAction;

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
     * @param FlushAction $flushAction
     * @param EntityType $entityType
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        FlushAction $flushAction,
        EntityType $entityType,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->flushAction = $flushAction;
        $this->entityType = $entityType;
        $this->logger = $logger;
    }

    /**
     * Starts the initial import
     *
     * @return Json
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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
            $this->flushAction->execute($entityType);
            $message = __(
                '%entity_type initial import is successfully flushed',
                [
                    'entity_type' => $this->entityType->getEntityName($entityType)
                ]
            );
            $resultJson->setHttpResponseCode(self::HTTP_OK);
            $resultJson->setData($this->getResponseContent(true, $message));
            $this->messageManager->addSuccessMessage($message);
        } catch (AuthenticationException $e) {
            $this->logger->error(
                __(
                    'Invalid credentials. Original error message: %error_message',
                    [
                        'error_message' => $e->getMessage()
                    ]
                )
            );
            $resultJson->setHttpResponseCode(self::HTTP_BAD_REQUEST);
            $resultJson->setData(
                $this->getResponseContent(
                    false,
                    __('Invalid credentials. See log for a detailed error message.')
                )
            );
        } catch (AuthorizationException $e) {
            $this->logger->error(
                __(
                    'You don\'t have permission to flush %entity_type initial import. '
                    . 'Original error message: %error_message',
                    [
                        'entity_type' => $this->entityType->getEntityName($entityType),
                        'error_message' => $e->getMessage(),
                    ]
                )
            );
            $resultJson->setHttpResponseCode(self::HTTP_BAD_REQUEST);
            $resultJson->setData(
                $this->getResponseContent(
                    false,
                    __(
                        'You don\'t have permission to flush %entity_type initial import. '
                        . 'See log for a detailed error message.',
                        [
                            'entity_type' => $this->entityType->getEntityName($entityType)
                        ]
                    )
                )
            );
        } catch (ValidationException $e) {
            $errors[] = $e->getMessage();

            foreach ($e->getErrors() as $exception) {
                $errors[] = $exception->getMessage();
            }

            $resultJson->setHttpResponseCode(self::HTTP_BAD_REQUEST);
            $resultJson->setData($this->getResponseContent(false, implode(' ', $errors)));
        } catch (LocalizedException $e) {
            $resultJson->setHttpResponseCode(self::HTTP_BAD_REQUEST);
            $resultJson->setData(
                $this->getResponseContent(
                    false,
                    __(
                        'Failed to flush %entity_type initial import. Original error message: %error_message',
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
                    'Failed to flush %entity_type initial import. Original error message: %error_message',
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
                        'Failed to flush %entity_type initial import. See log for a detailed error message.',
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
     * @param Phrase|string $message
     *
     * @return array
     */
    private function getResponseContent(bool $isSuccess, $message): array
    {
        return [
            'success' => $isSuccess,
            'message' => $message
        ];
    }
}
