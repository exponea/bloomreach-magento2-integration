<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Logger;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * The class is responsible for saving the debug information to the log file
 */
class Debugger
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $isDebugModeEnabled;

    /**
     * @param ConfigProvider $configProvider
     * @param SerializerInterface $jsonSerializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigProvider $configProvider,
        SerializerInterface $jsonSerializer,
        LoggerInterface $logger
    ) {
        $this->configProvider = $configProvider;
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $logger;
    }

    /**
     * Log debugging information
     *
     * @param Phrase|string $message
     *
     * @return void
     */
    public function log($message): void
    {
        if ($this->isDebugModeEnabled()) {
            $this->logger->debug($message);
        }
    }

    /**
     * Log debugging information
     *
     * @param string $placeholder
     * @param array $data
     *
     * @return void
     */
    public function logArray(string $placeholder, array $data): void
    {
        if ($this->isDebugModeEnabled()) {
            $this->logger->debug(__($placeholder, $this->jsonSerializer->serialize($data)));
        }
    }

    /**
     * Checks whether is debug mode enabled
     *
     * @return bool
     */
    private function isDebugModeEnabled(): bool
    {
        if ($this->isDebugModeEnabled === null) {
            $this->isDebugModeEnabled = $this->configProvider->isDebugModeEnabled();
        }

        return $this->isDebugModeEnabled;
    }
}
