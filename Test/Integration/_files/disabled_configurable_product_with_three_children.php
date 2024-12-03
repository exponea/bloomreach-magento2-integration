<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */

use Magento\Catalog\Api\Data\ProductExtensionInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Model\Stock\ItemFactory;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Eav\Model\Config;
use Magento\Catalog\Model\Product;

Resolver::getInstance()->requireDataFixture(
    'Bloomreach_EngagementConnector::Test/Integration/_files/configurable_attribute.php'
);

$objectManager = Bootstrap::getObjectManager();
/** @var Factory $optionsFactory */
$optionsFactory = $objectManager->get(Factory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var ProductExtensionInterfaceFactory $productExtensionFactory */
$productExtensionFactory = $objectManager->get(ProductExtensionInterfaceFactory::class);
/** @var ProductFactory $productFactory */
$productFactory = $objectManager->get(ProductFactory::class);
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$baseWebsite = $websiteRepository->get('base');
$eavConfig = Bootstrap::getObjectManager()->get(Config::class);
$attribute = $eavConfig->getAttribute(Product::ENTITY, 'test_configurable');
/** @var AttributeOptionInterface[] $options */
$options = $attribute->getOptions();
$associatedProductIds = $attributeValues = [];
$simpleProductsData = [
    ['simple_1', 10, 'Black', Status::STATUS_DISABLED, Visibility::VISIBILITY_NOT_VISIBLE],
    ['simple_2', 20, 'White', Status::STATUS_ENABLED, Visibility::VISIBILITY_NOT_VISIBLE],
    ['simple_3', 30, 'White', Status::STATUS_DISABLED, Visibility::VISIBILITY_BOTH],
];
foreach ($options as $option) {
    if (!$option->getValue()) {
        continue;
    }
    [$productSku, $productPrice, $productDescription, $status, $visibility] = array_shift($simpleProductsData);
    $product = $productFactory->create();
    $product->isObjectNew(true);
    $product->setTypeId(Type::TYPE_SIMPLE)
        ->setAttributeSetId($product->getDefaultAttributeSetId())
        ->setWebsiteIds([$baseWebsite->getId()])
        ->setName('Configurable ' . $option->getLabel())
        ->setSku($productSku)
        ->setPrice($productPrice)
        ->setTestConfigurable($option->getValue())
        ->setDescription($productDescription)
        ->setVisibility($visibility)
        ->setStatus($status)
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
    $product = $productRepository->save($product);
    $attributeValues[] = [
        'label' => 'test',
        'attribute_id' => $attribute->getId(),
        'value_index' => $option->getValue(),
    ];
    $associatedProductIds[] = $product->getId();
}
$product = $productFactory->create();
$product->isObjectNew(true);
$product->setTypeId(Configurable::TYPE_CODE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([$baseWebsite->getId()])
    ->setName('Configurable product with two child')
    ->setSku('configurable')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_DISABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
$configurableOptions = $optionsFactory->create(
    [
        [
            'attribute_id' => $attribute->getId(),
            'code' => $attribute->getAttributeCode(),
            'label' => $attribute->getStoreLabel(),
            'position' => '0',
            'values' => $attributeValues,
        ],
    ]
);
$extensionConfigurableAttributes = $product->getExtensionAttributes() ?? $productExtensionFactory->create();
$extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
$extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);
$product->setExtensionAttributes($extensionConfigurableAttributes);
$productRepository->save($product);