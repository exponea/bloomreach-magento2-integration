<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Ui\Component\Listing\ExportQueue\Column;

use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Column with actions for listing
 */
class Actions extends Column
{
    private const DELETE_PATH = 'bloomreach_engagement/exportQueue/delete';

    private const RETRY_PATH = 'bloomreach_engagement/exportQueue/retry';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
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
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        foreach ($dataSource['data']['items'] as &$item) {
            $item[$this->getData('name')]['delete'] = $this->getDeleteAction((int)$item['entity_id']);

            if ((int) $item[Status::ORIGINAL_STATUS_VALUE] === ExportQueueModel::STATUS_ERROR) {
                $item[$this->getData('name')]['retry'] = $this->getRetryAction((int)$item['entity_id']);
            }
        }

        return $dataSource;
    }

    /**
     * Get Delete Action
     *
     * @param int $entityId
     *
     * @return array
     */
    private function getDeleteAction(int $entityId): array
    {
        return [
            'href' => $this->urlBuilder->getUrl(
                self::DELETE_PATH,
                [
                    'id' => $entityId
                ]
            ),
            'label' => __('Delete'),
            'confirm' => [
                'title' => __('Delete the Item'),
                'message' => __('Are you sure you want to delete the item?')
            ]
        ];
    }

    /**
     * Get Retry Action
     *
     * @param int $entityId
     *
     * @return array
     */
    private function getRetryAction(int $entityId): array
    {
        return [
            'href' => $this->urlBuilder->getUrl(
                self::RETRY_PATH,
                [
                    'id' => $entityId
                ]
            ),
            'label' => __('Retry'),
            'confirm' => [
                'title' => __('Resend the Item'),
                'message' => __('Are you sure you want to resend the item?')
            ]
        ];
    }
}
