<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\ExportQueue;

use Bloomreach\EngagementConnector\System\ConfigProvider;

/**
 * Calculates the time (in Unix Timestamp) of next sending attempt using Exponential Backoff with Jitter approach
 */
class CalculateTimeOfNextSendingAttempt
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @param ConfigProvider $configProvider
     */
    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * Calculates the time of next sending attempt
     *
     * @param int $attempt
     *
     * @return int
     */
    public function execute(int $attempt): int
    {
        return time() + $this->getJitter($attempt);
    }

    /**
     * Get Exponential Backoff with Jitter
     *
     * Formula: random_between(0, exponential_backoff)
     *
     * @param int $attempt
     *
     * @return int
     */
    private function getJitter(int $attempt): int
    {
        return random_int(0, $this->getExponentialBackoff($attempt));
    }

    /**
     * Get Exponential Backoff
     *
     * Formula: min(max_wait_time, pow(attempt, 2) * base_base_time)
     *
     * @param int $attempt
     *
     * @return int
     */
    private function getExponentialBackoff(int $attempt): int
    {
        return (int) min(
            $this->configProvider->getRetryMaxWaitTime(),
            pow($attempt, 2) * $this->configProvider->getRetryBaseWaitTime()
        );
    }
}
