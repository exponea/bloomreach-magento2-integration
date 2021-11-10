<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Plugin\Customer\CustomerData;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;
use Magento\Customer\CustomerData\Customer as Subject;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Add registered data to the customer data
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AddRegisteredData
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @param CustomerSession $customerSession
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        CustomerSession $customerSession,
        ConfigProvider $configProvider
    ) {
        $this->customerSession = $customerSession;
        $this->configProvider = $configProvider;
    }

    /**
     * Add registered data to the customer data
     *
     * @param Subject $subject
     * @param array $result
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionData(Subject $subject, array $result): array
    {
        if (!$this->configProvider->isEnabled()) {
            return $result;
        }

        $customerId = $this->customerSession->getCustomerId();

        if ($customerId) {
            $result['registered'] = [
                'registered' => $this->customerSession->getCustomer()->getEmail(),
                'customer_id' => $customerId
            ];
        }

        return $result;
    }
}
