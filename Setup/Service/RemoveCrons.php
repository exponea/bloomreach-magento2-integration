<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Setup\Service;

use Exception;
use Magento\Cron\Model\ResourceModel\Schedule as ScheduleResource;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;

/**
 * Remove jobs from 'cron_scheduler' table
 */
class RemoveCrons
{
    /**
     * @var ScheduleCollectionFactory
     */
    private $scheduleCollectionFactory;

    /**
     * @var ScheduleResource
     */
    private $scheduleResource;

    /**
     * @param ScheduleCollectionFactory $scheduleCollectionFactory
     * @param ScheduleResource $scheduleResource
     */
    public function __construct(
        ScheduleCollectionFactory $scheduleCollectionFactory,
        ScheduleResource $scheduleResource
    ) {
        $this->scheduleResource = $scheduleResource;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
    }

    /**
     * Removing cron jobs
     *
     * @return void
     * @throws Exception
     */
    public function execute(): void
    {
        $scheduleCollection = $this->scheduleCollectionFactory->create()
            ->addFieldToFilter('job_code', ['like' => 'bloomreach%']);

        foreach ($scheduleCollection as $schedule) {
            $this->scheduleResource->delete($schedule);
        }
    }
}
