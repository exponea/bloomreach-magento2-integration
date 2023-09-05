<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Ui\Component\Listing\InitialImport\Column;

use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\InitialExportStatus;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Prepares data for a actions column
 */
class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var EntityType
     */
    private $entityType;

    /**
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param EntityType $entityType
     * @param array $components
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        EntityType $entityType,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
        $this->urlBuilder = $urlBuilder;
        $this->entityType = $entityType;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $item[$this->getData('name')] = $this->getActionByStatus($item);
        }

        return $dataSource;
    }

    /**
     * Get Action By Status
     *
     * @param array $item
     *
     * @return array
     */
    private function getActionByStatus(array $item): array
    {
        $entityName = $this->entityType->getEntityName($item['entity_type']);
        $statusCode = (int) $item[InitialExportStatus::STATUS];

        //TODO Add new actions
        switch ($statusCode) {
            case StatusSource::DISABLED:
                $action = [
                    'label' => __('Enable Feed'),
                    'link' => $this->urlBuilder->getUrl('bloomreach_engagement/initialImport/enable'),
                    'requestParams' => ['entity_type' => $item['entity_type']],
                    'cssClass' => 'action-default primary add',
                    'entityName' => $entityName,
                    'actionType' => __('enable')
                ];
                break;
            case StatusSource::NOT_READY:
                $action = [
                    'label' => __('Configure'),
                    'preview_link' => $this->urlBuilder->getUrl(
                        'bloomreach_engagement/initialImport/previewConfiguration'
                    ),
                    'link' => $this->urlBuilder->getUrl('bloomreach_engagement/initialImport/configure'),
                    'requestParams' => ['entity_type' => $item['entity_type']],
                    'cssClass' => 'action-secondary action-accept',
                    'modal_title' => __(
                        '%entity_type import configuration',
                        ['entity_type' => $entityName]
                    ),
                    'entityName' => $entityName,
                    'actionType' => __('configure')
                ];
                break;
            case StatusSource::READY:
                $action = [
                    'label' => __('Start'),
                    'link' => $this->urlBuilder->getUrl('bloomreach_engagement/initialImport/start'),
                    'requestParams' => ['entity_type' => $item['entity_type']],
                    'cssClass' => 'action-default primary add',
                    'entityName' => $entityName,
                    'actionType' => __('start')
                ];
                break;
            case StatusSource::SCHEDULED:
            case StatusSource::PROCESSING:
                $action = [
                    'label' => __('Stop'),
                    'link' => $this->urlBuilder->getUrl('bloomreach_engagement/initialImport/stop'),
                    'requestParams' => ['entity_type' => $item['entity_type']],
                    'cssClass' => 'action-secondary action-accept',
                    'entityName' => $entityName,
                    'actionType' => __('stop'),
                    'confirmation' => true
                ];
                break;
            case StatusSource::ERROR:
            case StatusSource::SUCCESS:
                $action = [
                    'label' => __('Flush'),
                    'link' => $this->urlBuilder->getUrl('bloomreach_engagement/initialImport/flush'),
                    'requestParams' => ['entity_type' => $item['entity_type']],
                    'cssClass' => 'action-default',
                    'entityName' => $entityName,
                    'actionType' => __('flush'),
                    'confirmation' => true
                ];
                break;
            default:
                $action = [];
        }

        return $action;
    }
}
