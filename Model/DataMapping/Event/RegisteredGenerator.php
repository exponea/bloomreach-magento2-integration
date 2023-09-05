<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\Event;

use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * The class is responsible for generating a registered key for events
 */
class RegisteredGenerator
{
    public const EMAIL_ID = 'email_id';

    public const CUSTOMER_ID = 'customer_id';

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var array
     */
    private $registeredMapping;

    /**
     * @param SerializerInterface $jsonSerializer
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        SerializerInterface $jsonSerializer,
        ConfigProvider $configProvider
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->configProvider = $configProvider;
    }

    /**
     * Generate registered key
     *
     * @param string $customerEmail
     * @param int|null $customerId
     *
     * @return array
     */
    public function generate(string $customerEmail, ?int $customerId): array
    {
        return $this->getRegistered($customerEmail, $customerId);
    }

    /**
     * Generate serialized registered key
     *
     * @param string $customerEmail
     * @param int|null $customerId
     *
     * @return string
     */
    public function generateSerialized(string $customerEmail, ?int $customerId): string
    {
        return $this->jsonSerializer->serialize($this->generate($customerEmail, $customerId));
    }

    /**
     * Get Registered Mapping
     *
     * @return string[]
     */
    public function getRegisteredMapping(): array
    {
        if ($this->registeredMapping === null) {
            $this->registeredMapping = [
                self::EMAIL_ID => $this->configProvider->getEmailFieldMapping() ?: self::EMAIL_ID,
                self::CUSTOMER_ID => $this->configProvider->getCustomerIdFieldMapping() ?: self::CUSTOMER_ID
            ];
        }

        return $this->registeredMapping;
    }

    /**
     * Delete registered data from properties
     *
     * @param array $properties
     *
     * @return void
     */
    public function deleteRegisteredData(array &$properties): void
    {
        $registeredKeys = array_keys($this->getRegistered());

        foreach ($registeredKeys as $key) {
            if (isset($properties[$key])) {
                unset($properties[$key]);
            }
        }
    }

    /**
     * Returns registered
     *
     * @param string|null $customerEmail
     * @param int|null $customerId
     *
     * @return array
     */
    private function getRegistered(?string $customerEmail = null, ?int $customerId = null): array
    {
        $registeredMapping = $this->getRegisteredMapping();

        return [
            $registeredMapping[self::EMAIL_ID] => $customerEmail,
            $registeredMapping[self::CUSTOMER_ID] => $customerId
        ];
    }
}
