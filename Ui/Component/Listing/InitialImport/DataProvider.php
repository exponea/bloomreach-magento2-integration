<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Ui\Component\Listing\InitialImport;

use Bloomreach\EngagementConnector\Api\Data\InitialExportStatusInterface;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\InitialExportStatus;
use Bloomreach\EngagementConnector\Service\InitialExportStatus\ItemsGetter as InitialExportStatusList;
use InvalidArgumentException;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as BaseDataProvider;

/**
 * Data provider for the list of imports
 */
class DataProvider extends BaseDataProvider
{
    /**
     * @var InitialExportStatusList
     */
    private $initialExportStatusList;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param InitialExportStatusList $initialExportStatusList
     * @param SerializerInterface $jsonSerializer
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        InitialExportStatusList $initialExportStatusList,
        SerializerInterface $jsonSerializer,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->initialExportStatusList = $initialExportStatusList;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Get data
     *
     * @return array|array[]
     */
    public function getData()
    {
        $result = [
            'items' => []
        ];

        $items = $this->initialExportStatusList->execute();

        ksort($items);

        foreach ($items as $item) {
            $result['items'][] = $this->prepareItem($item);
        }

        $result['totalRecords'] = count($result['items']);

        return $result;
    }

    /**
     * Prepare item
     *
     * @param InitialExportStatusInterface $initialExportStatus
     *
     * @return array
     */
    private function prepareItem(InitialExportStatusInterface $initialExportStatus): array
    {
        $item = [];
        $item[InitialExportStatus::ENTITY_TYPE] = $initialExportStatus->getEntityType();
        $item[InitialExportStatus::STATUS] = $initialExportStatus->getStatus();
        $item[InitialExportStatus::TOTAL_ITEMS] = $initialExportStatus->getTotalItems();
        $item[InitialExportStatus::TOTAL_EXPORTED] = $initialExportStatus->getTotalExported();
        $item[InitialExportStatus::TOTAL_ERROR_ITEMS] = $initialExportStatus->getTotalErrorItems();
        $item[InitialExportStatus::STARTED_AT] = $initialExportStatus->getStartedAt() ?: '0000-00-00 00:00:00';
        $item[InitialExportStatus::FINISHED_AT] = $initialExportStatus->getFinishedAt() ?: '0000-00-00 00:00:00';
        $item[InitialExportStatus::ERRORS] = $this->getErrors($initialExportStatus);
        $item['progress'] = $this->getProgress($initialExportStatus);

        return $item;
    }

    /**
     * Get progress
     *
     * @param InitialExportStatusInterface $initialExportStatus
     *
     * @return string
     */
    private function getProgress(InitialExportStatusInterface $initialExportStatus): string
    {
        $totalProcessed = $initialExportStatus->getTotalExported() + $initialExportStatus->getTotalErrorItems();
        $totalItems = $initialExportStatus->getTotalItems() > 0 ? $initialExportStatus->getTotalItems() : 1;

        return sprintf(
            '%s%%',
            number_format($totalProcessed / $totalItems  * 100, 0)
        );
    }

    /**
     * Get Errors
     *
     * @param InitialExportStatusInterface $initialExportStatus
     *
     * @return array
     */
    private function getErrors(InitialExportStatusInterface $initialExportStatus): array
    {
        try {
            return $this->jsonSerializer->unserialize($initialExportStatus->getErrors());
        } catch (InvalidArgumentException $e) {
            return [];
        }
    }
}
