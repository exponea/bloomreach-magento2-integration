<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */

use Magento\Customer\Model\Customer;
use Magento\Framework\Registry;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var Customer $customer */
$customer = Bootstrap::getObjectManager()->create(
    Customer::class
);
$customer->load(1);
if ($customer->getId()) {
    $customer->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

/* Unlock account if it was locked for tokens retrieval */
/** @var RequestThrottler $throttler */
$throttler = Bootstrap::getObjectManager()->create(RequestThrottler::class);
$throttler->resetAuthenticationFailuresCount('customer@example.com', RequestThrottler::USER_TYPE_CUSTOMER);
