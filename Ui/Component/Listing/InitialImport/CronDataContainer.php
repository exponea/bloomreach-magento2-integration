<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Ui\Component\Listing\InitialImport;

use Bloomreach\EngagementConnector\Cron\AddToExportQueueRunner;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource;
use Bloomreach\EngagementConnector\Service\InitialExportStatus\ItemsGetter;
use Bloomreach\EngagementConnector\Ui\Component\Listing\GetCronDataContainerConfig;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Container;

/**
 * The 'export queue' cron job data container
 */
class CronDataContainer extends Container
{
    /**
     * @var ItemsGetter
     */
    private $itemsGetter;

    /**
     * @var GetCronDataContainerConfig
     */
    private $getCronDataContainerConfig;

    /**
     * @param ContextInterface $context
     * @param ItemsGetter $itemsGetter
     * @param GetCronDataContainerConfig $getCronDataContainerConfig
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        ItemsGetter $itemsGetter,
        GetCronDataContainerConfig $getCronDataContainerConfig,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $components,
            $data
        );
        $this->itemsGetter = $itemsGetter;
        $this->getCronDataContainerConfig = $getCronDataContainerConfig;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare(): void
    {
        parent::prepare();
        if (!$this->isVisible()) {
            return;
        }

        $config = $this->getData('config');
        if (!is_array($config)) {
            $config = [];
        }

        $this->setData(
            'config',
            $this->getCronDataContainerConfig->execute($config, AddToExportQueueRunner::CRON_JOB_CODE)
        );
    }

    /**
     * Component should be visible if at least one import has a status of Scheduled or Processing
     *
     * @return bool
     */
    private function isVisible(): bool
    {
        $initialExportStatusList = $this->itemsGetter->execute();
        foreach ($initialExportStatusList as $initialExportStatus) {
            $status = $initialExportStatus->getStatus();
            if (in_array($status, [StatusSource::SCHEDULED, StatusSource::PROCESSING], true)) {
                return true;
            }
        }

        return false;
    }
}
