<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Block\Adminhtml\ExportQueue\Listing\Button;

use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Download Debug Log File Button Block
 */
class DownloadDebugLogFile implements ButtonProviderInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param ConfigProvider $configProvider
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        ConfigProvider $configProvider,
        UrlInterface $urlBuilder
    ) {
        $this->configProvider = $configProvider;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Retrieve button-specified settings
     *
     * @return array
     */
    public function getButtonData()
    {
        if (!$this->configProvider->isDebugModeEnabled()) {
            return [];
        }

        return [
            'label' => __('Download Debug Log File'),
            'url' => $this->urlBuilder->getUrl(
                'bloomreach_engagement/exportQueue/exportLogFile',
                ['fileName' => 'bloomreach/debug.log']
            ),
            'class' => 'primary',
            'sort_order' => 10
        ];
    }
}
