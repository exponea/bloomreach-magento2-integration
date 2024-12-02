<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Registry;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$eavConfig = $objectManager->get(Config::class);
$attribute = $eavConfig->getAttribute('catalog_product', 'test_configurable');
if ($attribute instanceof AbstractAttribute && $attribute->getId()) {
    $attribute->delete();
}
$eavConfig->clear();
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
