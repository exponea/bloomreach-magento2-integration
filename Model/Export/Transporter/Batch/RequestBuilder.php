<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Transporter\Batch;

use Bloomreach\EngagementConnector\Block\Adminhtml\System\Config\ModuleVersion;
use Bloomreach\EngagementConnector\Model\Export\Queue\Batch\CommandsListBuilder;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * The class is responsible for building batch request data
 */
class RequestBuilder
{
    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    /**
     * @var CommandsListBuilder
     */
    private $commandsListBuilder;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var string
     */
    private $moduleVersion;

    /**
     * @param ResourceInterface $moduleResource
     * @param CommandsListBuilder $commandsListBuilder
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        ResourceInterface $moduleResource,
        CommandsListBuilder $commandsListBuilder,
        ProductMetadataInterface $productMetadata
    ) {
        $this->moduleResource = $moduleResource;
        $this->commandsListBuilder = $commandsListBuilder;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Builds request
     *
     * @param array $exportQueueList
     *
     * @return array
     */
    public function build(array $exportQueueList): array
    {
        return [
            'sdk' => 'magento-plugin',
            'sdk_version' => $this->getModuleVersion(),
            'magento_version' => sprintf('v%s', $this->productMetadata->getVersion()),
            'commands' => $this->commandsListBuilder->build($exportQueueList)
        ];
    }

    /**
     * Get module version
     *
     * @return string
     */
    private function getModuleVersion(): string
    {
        if ($this->moduleVersion === null) {
            $this->moduleVersion = sprintf('v%s', $this->moduleResource->getDbVersion(ModuleVersion::MODULE_NAME));
        }

        return $this->moduleVersion;
    }
}
