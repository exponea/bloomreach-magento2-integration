<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Cron;

use Magento\Cron\Model\ResourceModel\Schedule\Collection as ScheduleCollection;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;
use Magento\Cron\Model\Schedule;
use Zend_Db_Expr;

/**
 * Get next 'pending' cron job `scheduled_at` date by `job_code`
 */
class GetNextPendingCronJobScheduledDate
{
    /**
     * @var ScheduleCollectionFactory
     */
    private $scheduleCollectionFactory;

    /**
     * @param ScheduleCollectionFactory $scheduleCollectionFactory
     */
    public function __construct(ScheduleCollectionFactory $scheduleCollectionFactory)
    {
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
    }

    /**
     * Get next 'pending' cron job `scheduled_at` date by `job_code`
     *
     * @param string $jobCode
     *
     * @return string|null
     */
    public function execute(string $jobCode): ?string
    {
        /** @var ScheduleCollection $collection */
        $collection = $this->scheduleCollectionFactory->create();
        $collection->addFieldToFilter('job_code', $jobCode);
        $collection->addFieldToFilter('status', Schedule::STATUS_PENDING);
        $collection->addFieldToFilter('scheduled_at', ['gt' => new Zend_Db_Expr('NOW()')]);
        $collection->setOrder('scheduled_at', $collection::SORT_ORDER_ASC);
        $collection->setPageSize(1);
        $schedule = $collection->getFirstItem();
        if ($schedule->getData('schedule_id') === null) {
            return null;
        }

        return (string)$schedule->getData('scheduled_at') ?? null;
    }
}
