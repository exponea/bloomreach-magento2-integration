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
 * Prepares data for a progress log column
 */
class ProgressLog extends Column
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
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param EntityType $entityType
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
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
            if (in_array($item[InitialExportStatus::STATUS], StatusSource::PROGRESS_LOG_VISIBLE_STATUSES)) {
                $item[$this->getData('name')]['view_progress'] = [
                    'label' => __('View Progress'),
                    'url' => $this->urlBuilder->getUrl('bloomreach_engagement/initialImport/loadProgress'),
                    'requestParams' => ['entity_type' => $item[InitialExportStatus::ENTITY_TYPE]],
                    'entityName' => $this->entityType->getEntityName($item[InitialExportStatus::ENTITY_TYPE])
                ];
            }

            if ($item[InitialExportStatus::ERRORS]) {
                $item[$this->getData('name')][InitialExportStatus::ERRORS] = [
                    'label' => __('View Errors')
                ];
            }
        }

        return $dataSource;
    }
}
