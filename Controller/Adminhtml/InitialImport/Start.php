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
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Start as StartAction;
use Bloomreach\EngagementConnector\Service\Integration\GetCatalog;
use Bloomreach\EngagementConnector\Service\Integration\GetImport;
use Bloomreach\EngagementConnector\System\CatalogIdResolver;
use Bloomreach\EngagementConnector\System\ConfigPathGetter;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Bloomreach\EngagementConnector\System\ImportIdResolver;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Phrase;
use Magento\Framework\Validation\ValidationException;
use Psr\Log\LoggerInterface;

/**
 * The class is responsible for starting the initial import
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Start extends Action implements HttpPostActionInterface
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
     * @var StartAction
     */
    private $startAction;

    /**
     * @var EntityType
     */
    private $entityType;

    /**
     * @var GetImport
     */
    private $getImport;

    /**
     * @var ImportIdResolver
     */
    private $importIdResolver;

    /**
     * @var ConfigPathGetter
     */
    private $configPathGetter;

    /**
     * @var CatalogIdResolver
     */
    private $catalogIdResolver;

    /**
     * @var GetCatalog
     */
    private $getCatalog;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param StartAction $startAction
     * @param EntityType $entityType
     * @param GetImport $getImport
     * @param ImportIdResolver $importIdResolver
     * @param ConfigPathGetter $configPathGetter
     * @param CatalogIdResolver $catalogIdResolver
     * @param GetCatalog $getCatalog
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        StartAction $startAction,
        EntityType $entityType,
        GetImport $getImport,
        ImportIdResolver $importIdResolver,
        ConfigPathGetter $configPathGetter,
        CatalogIdResolver $catalogIdResolver,
        GetCatalog $getCatalog,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->startAction = $startAction;
        $this->entityType = $entityType;
        $this->getImport = $getImport;
        $this->importIdResolver = $importIdResolver;
        $this->configPathGetter = $configPathGetter;
        $this->catalogIdResolver = $catalogIdResolver;
        $this->getCatalog = $getCatalog;
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
            $this->getImport->execute($this->importIdResolver->getImportId($entityType));
            $this->validateCatalog($entityType);
            $this->startAction->execute($entityType);
            $message = __(
                '%entity_type initial import is successfully scheduled',
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
                    'You don\'t have permission to start %entity_type initial import. '
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
                        'You don\'t have permission to start %entity_type initial import. '
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
                        'Failed to start %entity_type initial import. Original error message: %error_message',
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
                    'Failed to start %entity_type initial import. Original error message: %error_message',
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
                        'Failed to start %entity_type initial import. See log for a detailed error message.',
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

    /**
     * Validates Catalog
     *
     * @param string $entityType
     *
     * @return void
     * @throws LocalizedException
     * @throws ValidatorException
     * @throws NoSuchEntityException
     */
    private function validateCatalog(string $entityType): void
    {
        if (!$this->getCatalogIdConfigPath($entityType)) {
            return;
        }

        $this->getCatalog->execute($this->catalogIdResolver->getCatalogId($entityType));
    }

    /**
     * Get Catalog ID configPath
     *
     * @param string $entityType
     *
     * @return string
     */
    private function getCatalogIdConfigPath(string $entityType): string
    {
        try {
            return $this->configPathGetter->get($entityType, ConfigProvider::CATALOG_ID_TYPE);
        } catch (LocalizedException $e) {
            return '';
        }
    }
}
