<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Ui\Component\Listing\ExportQueue;

use Bloomreach\EngagementConnector\Cron\ExportRunner;
use Bloomreach\EngagementConnector\Ui\Component\Listing\GetCronDataContainerConfig;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Container;

/**
 * The 'run export' cron job data container
 */
class CronDataContainer extends Container
{
    /**
     * @var GetCronDataContainerConfig
     */
    private $getCronDataContainerConfig;

    /**
     * @param ContextInterface $context
     * @param GetCronDataContainerConfig $getCronDataContainerConfig
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        GetCronDataContainerConfig $getCronDataContainerConfig,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $components,
            $data
        );
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
        $config = $this->getData('config');
        if (!is_array($config)) {
            $config = [];
        }

        $this->setData(
            'config',
            $this->getCronDataContainerConfig->execute($config, ExportRunner::CRON_JOB_CODE)
        );
    }
}
