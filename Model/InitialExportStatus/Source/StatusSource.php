<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExportStatus\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Phrase;

/**
 * Initial Export Statuses source
 */
class StatusSource implements OptionSourceInterface
{
    public const DISABLED = 1;

    public const NOT_READY = 2;

    public const READY = 3;

    public const SCHEDULED = 4;

    public const PROCESSING = 5;

    public const ERROR = 6;

    public const SUCCESS = 7;

    /**
     * Get Statuses Source
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::DISABLED,
                'label' => __('Disabled')
            ],
            [
                'value' => self::NOT_READY,
                'label' => __('Not Ready')
            ],
            [
                'value' => self::READY,
                'label' => __('Ready')
            ],
            [
                'value' => self::SCHEDULED,
                'label' => __('Scheduled')
            ],
            [
                'value' => self::PROCESSING,
                'label' => __('Processing')
            ],
            [
                'value' => self::ERROR,
                'label' => __('Error')
            ],
            [
                'value' => self::SUCCESS,
                'label' => __('Success')
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
