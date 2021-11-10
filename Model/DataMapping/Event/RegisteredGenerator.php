<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\Event;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * The class is responsible for generating a registered key for order event
 */
class RegisteredGenerator
{
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
     * Generate registered key
     *
     * @param string $customerEmail
     * @param int|null $customerId
     *
     * @return string
     */
    public function execute(string $customerEmail, ?int $customerId): string
    {
        return $this->jsonSerializer->serialize(
            [
                'registered' => $customerEmail,
                'customer_id' => $customerId
            ]
        );
    }
}
