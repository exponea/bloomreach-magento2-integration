<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\Event;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * The class is responsible for generating a registered key for events
 */
class RegisteredGenerator
{
    const EMAIL_ID = 'email_id';

    const CUSTOMER_ID = 'customer_id';

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @param SerializerInterface $jsonSerializer
     */
    public function __construct(SerializerInterface $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
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
     * Returns registered
     *
     * @param string|null $customerEmail
     * @param int|null $customerId
     *
     * @return array
     */
    private function getRegistered(?string $customerEmail = null, ?int $customerId = null): array
    {
        return [self::EMAIL_ID => $customerEmail, self::CUSTOMER_ID => $customerId];
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
}
