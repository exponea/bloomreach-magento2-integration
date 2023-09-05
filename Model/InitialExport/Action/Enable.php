<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action;

use Bloomreach\EngagementConnector\Model\InitialExport\Action\Validator\ActionExecute as ActionExecuteValidator;
use Bloomreach\EngagementConnector\Service\InitialExportStatus\ItemGetter as InitialExportStatusGetter;
use Bloomreach\EngagementConnector\System\ConfigPathGetter;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * This class is responsible for enabling initial import
 */
class Enable
{
    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ConfigPathGetter
     */
    private $configPathGetter;

    /**
     * @var CacheTypeList
     */
    private $cacheTypeList;

    /**
     * @var InitialExportStatusGetter
     */
    private $initialExportStatusGetter;

    /**
     * @var ActionExecuteValidator
     */
    private $actionExecuteValidator;

    /**
     * @param WriterInterface $configWriter
     * @param ConfigPathGetter $configPathGetter
     * @param CacheTypeList $cacheTypeList
     * @param InitialExportStatusGetter $initialExportStatusGetter
     * @param ActionExecuteValidator $actionExecuteValidator
     */
    public function __construct(
        WriterInterface $configWriter,
        ConfigPathGetter $configPathGetter,
        CacheTypeList $cacheTypeList,
        InitialExportStatusGetter $initialExportStatusGetter,
        ActionExecuteValidator $actionExecuteValidator
    ) {
        $this->configWriter = $configWriter;
        $this->configPathGetter = $configPathGetter;
        $this->cacheTypeList = $cacheTypeList;
        $this->initialExportStatusGetter = $initialExportStatusGetter;
        $this->actionExecuteValidator = $actionExecuteValidator;
    }

    /**
     * Enables Initial Import
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
            ActionExecuteValidator::ENABLE_ACTION
        );
        $this->configWriter->save(
            $this->configPathGetter->get($entityType, ConfigProvider::FEED_ENABLED_TYPE),
            1,
            $scope,
            $scopeId
        );
        $this->cacheTypeList->cleanType('config');
    }
}
