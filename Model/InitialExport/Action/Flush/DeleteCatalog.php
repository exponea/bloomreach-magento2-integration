<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Flush;

use Bloomreach\EngagementConnector\Service\Integration\DeleteCatalog as DeleteCatalogRequest;
use Bloomreach\EngagementConnector\System\CatalogIdResolver;
use Bloomreach\EngagementConnector\System\ConfigPathGetter;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * The class is responsible for deleting the catalog
 */
class DeleteCatalog
{
    /**
     * @var DeleteCatalogRequest
     */
    private $deleteCatalog;

    /**
     * @var ConfigPathGetter
     */
    private $configPathGetter;

    /**
     * @var CatalogIdResolver
     */
    private $catalogIdResolver;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @param DeleteCatalogRequest $deleteCatalog
     * @param ConfigPathGetter $configPathGetter
     * @param CatalogIdResolver $catalogIdResolver
     * @param WriterInterface $configWriter
     */
    public function __construct(
        DeleteCatalogRequest $deleteCatalog,
        ConfigPathGetter $configPathGetter,
        CatalogIdResolver $catalogIdResolver,
        WriterInterface $configWriter
    ) {
        $this->deleteCatalog = $deleteCatalog;
        $this->configPathGetter = $configPathGetter;
        $this->catalogIdResolver = $catalogIdResolver;
        $this->configWriter = $configWriter;
    }

    /**
     * Deletes the catalog
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
        $catalogIdConfigPath = $this->getConfigPath($entityType, ConfigProvider::CATALOG_ID_TYPE);

        if (!$catalogIdConfigPath) {
            return;
        }

        try {
            $this->deleteCatalog->execute($this->catalogIdResolver->getCatalogId($entityType, $scope, $scopeId));
            $this->deleteCatalogConfigs($entityType, $scope, $scopeId);
        } catch (NoSuchEntityException $e) {
            $this->deleteCatalogConfigs($entityType, $scope, $scopeId);
        }
    }

    /**
     * Delete catalog configs
     *
     * @param string $entityType
     * @param string $scope
     * @param int $scopeId
     *
     * @return void
     */
    private function deleteCatalogConfigs(string $entityType, string $scope, int $scopeId): void
    {
        $this->configWriter->delete(
            $this->getConfigPath($entityType, ConfigProvider::CATALOG_ID_TYPE),
            $scope,
            $scopeId
        );

        $this->configWriter->delete(
            $this->getConfigPath($entityType, ConfigProvider::REALTIME_UPDATE_TYPE),
            $scope,
            $scopeId
        );
    }

    /**
     * Get Config Path
     *
     * @param string $entityType
     * @param string $configType
     *
     * @return string
     */
    private function getConfigPath(string $entityType, string $configType): string
    {
        try {
            return $this->configPathGetter->get($entityType, $configType);
        } catch (LocalizedException $e) {
            return '';
        }
    }
}
