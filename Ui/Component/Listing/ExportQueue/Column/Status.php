<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Ui\Component\Listing\ExportQueue\Column;

use Bloomreach\EngagementConnector\Model\Export\Queue\Source\StatusSource;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Status column decorator
 */
class Status extends Column
{
    public const ORIGINAL_STATUS_VALUE = 'original_status';

    /**
     * @var StatusSource
     */
    private $statusSource;

    /**
     * @param StatusSource $statusSource
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        StatusSource $statusSource,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->statusSource = $statusSource;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }
    /**
     * Decorate the status column based on the provided value.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[self::ORIGINAL_STATUS_VALUE] = $item[ExportQueueModel::STATUS];
                $item[ExportQueueModel::STATUS] = $this->getDecoratedColumn((int) $item[ExportQueueModel::STATUS]);
            }
        }

        return $dataSource;
    }

    /**
     * Get the decorated element by value.
     *
     * @param int $value
     *
     * @return string
     */
    private function getDecoratedColumn(int $value): string
    {
        $rowLabel = $this->statusSource->getStatusLabel($value);

        switch ($value) {
            case ExportQueueModel::STATUS_COMPLETE:
                $rowClass = 'notice';
                break;
            case ExportQueueModel::STATUS_ERROR:
                $rowClass = 'major';
                break;
            default:
                $rowClass = 'minor';
                break;
        }

        return sprintf('<span class="grid-severity-%s"><span>%s</span></span>', $rowClass, $rowLabel);
    }
}
