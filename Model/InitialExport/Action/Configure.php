<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action;

use Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Catalog\Create as CreateCatalog;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Create as CreateImport;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Validator\ActionExecute as ActionExecuteValidator;
use Bloomreach\EngagementConnector\Service\InitialExportStatus\ItemGetter as InitialExportStatusGetter;
use Bloomreach\EngagementConnector\System\ConfigPathGetter;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * The class is responsible for configuring the initial import
 */
class Configure
{
    /**
     * @var CreateImport
     */
    private $createImport;

    /**
     * @var CacheTypeList
     */
    private $cacheTypeList;

    /**
     * @var ConfigPathGetter
     */
    private $configPathGetter;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var CreateCatalog
     */
    private $createCatalog;

    /**
     * @var InitialExportStatusGetter
     */
    private $initialExportStatusGetter;

    /**
     * @var ActionExecuteValidator
     */
    private $actionExecuteValidator;

    /**
     * @param CreateImport $createImport
     * @param CreateCatalog $createCatalog
     * @param WriterInterface $configWriter
     * @param ConfigPathGetter $configPathGetter
     * @param CacheTypeList $cacheTypeList
     * @param InitialExportStatusGetter $initialExportStatusGetter
     * @param ActionExecuteValidator $actionExecuteValidator
     */
    public function __construct(
        CreateImport $createImport,
        CreateCatalog $createCatalog,
        WriterInterface $configWriter,
        ConfigPathGetter $configPathGetter,
        CacheTypeList $cacheTypeList,
        InitialExportStatusGetter $initialExportStatusGetter,
        ActionExecuteValidator $actionExecuteValidator
    ) {
        $this->createImport = $createImport;
        $this->createCatalog = $createCatalog;
        $this->configWriter = $configWriter;
        $this->configPathGetter = $configPathGetter;
        $this->cacheTypeList = $cacheTypeList;
        $this->initialExportStatusGetter = $initialExportStatusGetter;
        $this->actionExecuteValidator = $actionExecuteValidator;
    }

    /**
     * Configures Initial Import
     *
     * @param string $entityType
     * @param string $scope
     * @param int $scopeId
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute(
        string $entityType,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        int $scopeId = 0
    ): void {
        $this->actionExecuteValidator->validate(
            $this->initialExportStatusGetter->execute($entityType),
            ActionExecuteValidator::CONFIGURE_ACTION
        );

        $catalogIdConfigPath = $this->getCatalogIdConfigPath($entityType);

        if ($catalogIdConfigPath) {
            $catalogId = $this->createCatalog->execute($entityType);
            $this->configWriter->save(
                $catalogIdConfigPath,
                $catalogId,
                $scope,
                $scopeId
            );
        }

        $importId = $this->createImport->execute($entityType);
        $this->configWriter->save(
            $this->configPathGetter->get($entityType, ConfigProvider::IMPORT_ID_TYPE),
            $importId,
            $scope,
            $scopeId
        );

        $this->cacheTypeList->cleanType('config');
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
