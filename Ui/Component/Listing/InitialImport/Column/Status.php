<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Ui\Component\Listing\InitialImport\Column;

use Bloomreach\EngagementConnector\Model\InitialExportStatus\InitialExportStatus;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Status column decorator
 */
class Status extends Column
{
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
                $item['decorated_status'] = $this->getDecoratedColumn((int) $item[InitialExportStatus::STATUS]);
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
            case StatusSource::SUCCESS:
            case StatusSource::READY:
                $rowClass = 'notice';
                break;
            case StatusSource::PROCESSING:
            case StatusSource::SCHEDULED:
                $rowClass = 'minor';
                break;
            default:
                $rowClass = 'major';
                break;
        }

        return sprintf('<span class="grid-severity-%s"><span>%s</span></span>', $rowClass, __($rowLabel));
    }
}
