<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Flush;

use Bloomreach\EngagementConnector\Service\Integration\DeleteImport as DeleteImportRequest;
use Bloomreach\EngagementConnector\System\ConfigPathGetter;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Bloomreach\EngagementConnector\System\ImportIdResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * The class is responsible for deleting the import
 */
class DeleteImport
{
    /**
     * @var DeleteImportRequest
     */
    private $deleteImport;

    /**
     * @var ImportIdResolver
     */
    private $importIdResolver;

    /**
     * @var ConfigPathGetter
     */
    private $configPathGetter;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @param DeleteImportRequest $deleteImport
     * @param ImportIdResolver $importIdResolver
     * @param ConfigPathGetter $configPathGetter
     * @param WriterInterface $configWriter
     */
    public function __construct(
        DeleteImportRequest $deleteImport,
        ImportIdResolver $importIdResolver,
        ConfigPathGetter $configPathGetter,
        WriterInterface $configWriter
    ) {
        $this->deleteImport = $deleteImport;
        $this->importIdResolver = $importIdResolver;
        $this->configPathGetter = $configPathGetter;
        $this->configWriter = $configWriter;
    }

    /**
     * Deletes an import
     *
     * @param string $entityType
     * @param string $scope
     * @param int $scopeId
     *
     * @return void
     * @throws CouldNotDeleteException
     * @throws LocalizedException
     */
    public function execute(
        string $entityType,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        int $scopeId = 0
    ): void {
        try {
            $this->deleteImport->execute($this->importIdResolver->getImportId($entityType, $scope, $scopeId));
            $this->deleteImportIdConfig($entityType, $scope, $scopeId);
        } catch (NoSuchEntityException $e) {
            $this->deleteImportIdConfig($entityType, $scope, $scopeId);
        }
    }

    /**
     * Deletes import ID config
     *
     * @param string $entityType
     * @param string $scope
     * @param int $scopeId
     *
     * @return void
     * @throws LocalizedException
     */
    private function deleteImportIdConfig(
        string $entityType,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        int $scopeId = 0
    ): void {
        $this->configWriter->delete(
            $this->configPathGetter->get($entityType, ConfigProvider::IMPORT_ID_TYPE),
            $scope,
            $scopeId
        );
    }
}
