<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Queue\Source;

use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Phrase;

/**
 * Export Queue statuses source
 */
class StatusSource implements OptionSourceInterface
{
    /**
     * Get Statuses Source
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => ExportQueueModel::STATUS_NEW,
                'label' => __('Pending')
            ],
            [
                'value' => ExportQueueModel::STATUS_IN_PROGRESS,
                'label' => __('In Progress')
            ],
            [
                'value' => ExportQueueModel::STATUS_ERROR,
                'label' => __('Error')
            ],
            [
                'value' => ExportQueueModel::STATUS_COMPLETE,
                'label' => __('Complete')
            ]
        ];
    }

    /**
     * Convert status label to value.
     *
     * @param int $statusCode
     *
     * @return string|Phrase
     */
    public function getStatusLabel(int $statusCode)
    {
        foreach ($this->toOptionArray() as $option) {
            if ($option['value'] === $statusCode) {
                return $option['label'];
            }
        }

        return '';
    }
}
