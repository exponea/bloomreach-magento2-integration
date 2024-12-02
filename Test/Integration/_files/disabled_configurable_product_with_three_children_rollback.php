<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
foreach (['simple_1', 'simple_2', 'configurable'] as $sku) {
    try {
        $productRepository->deleteById($sku);
    } catch (NoSuchEntityException $e) {
        // Product already deleted
    }
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
Resolver::getInstance()->requireDataFixture(
    'Bloomreach_EngagementConnector::Test/Integration/_files/configurable_attribute_rollback.php'
);
