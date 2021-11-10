<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Directory\Model\PriceCurrency;
use Magento\Directory\Model\ResourceModel\Currency;

/**
 * The class contains method for retrieve product prices
 */
class PriceDataResolver
{
    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var bool
     */
    private $isConvertRequire;

    /**
     * @var array
     */
    private $priceCache = [];

    /**
     * @var float
     */
    private $baseRate;

    /**
     * @param PriceCurrency $priceCurrency
     * @param Currency $currency
     */
    public function __construct(
        PriceCurrency $priceCurrency,
        Currency $currency
    ) {
        $this->currency = $currency;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Returns final price with base currency
     *
     * @param ProductInterface $product
     *
     * @return float
     */
    public function getBaseFinalPrice(ProductInterface $product): float
    {
        if (!isset($this->priceCache[$product->getId()]['base_final_price'])) {
            $price = $this->getFinalPrice($product);
            $this->priceCache[$product->getId()]['base_final_price'] = $price;

            if (!$this->isConvertRequire($product)) {
                return (float) number_format(
                    (float) $this->priceCache[$product->getId()]['base_final_price'],
                    2
                );
            }

            $rate = $this->getBaseRate($product);

            $this->priceCache[$product->getId()]['base_final_price'] =
                $rate ? round((float)($price * $rate), 2) : $price;
        }

        return (float) number_format((float) $this->priceCache[$product->getId()]['base_final_price'], 2);
    }

    /**
     * Returns product final price
     *
     * @param ProductInterface $product
     *
     * @return float
     */
    public function getFinalPrice(ProductInterface $product): float
    {
        if (!isset($this->priceCache[$product->getId()]['final_price'])) {
            $this->priceCache[$product->getId()]['final_price'] =
                (float) $product->getPriceInfo()->getPrice('final_price')->getValue();
        }

        return (float) number_format((float) $this->priceCache[$product->getId()]['final_price'], 2);
    }

    /**
     * Checks whether is convert require
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    private function isConvertRequire(ProductInterface $product): bool
    {
        if ($this->isConvertRequire === null) {
            $store = $product->getStore();
            $baseCurrency = $store->getBaseCurrency();
            $currentCurrency = $store->getCurrentCurrency();

            $this->isConvertRequire = $baseCurrency->getCode() !== $currentCurrency->getCode();
        }

        return $this->isConvertRequire;
    }

    /**
     * Get base rate
     *
     * @param ProductInterface $product
     *
     * @return float
     */
    private function getBaseRate(ProductInterface $product): float
    {
        if ($this->baseRate === null) {
            $store = $product->getStore();
            $this->baseRate = (float) $this->currency->getRate(
                $store->getCurrentCurrency(),
                $store->getBaseCurrency()
            );
        }

        return $this->baseRate;
    }

    /**
     * Returns original price for selected currency
     *
     * @param ProductInterface $product
     *
     * @return float
     */
    public function getOriginalPriceLocalCurrency(ProductInterface $product): float
    {
        if (!isset($this->priceCache[$product->getId()]['regular_price'])) {
            $price = (float) $product->getPriceInfo()->getPrice('regular_price')->getValue();
            $this->priceCache[$product->getId()]['regular_price'] = $price;

            if ($product->getTypeId() !== Configurable::TYPE_CODE) {
                (float) number_format((float) $this->priceCache[$product->getId()]['regular_price'], 2);
            }

            if ($this->isConvertRequire($product)) {
                $this->priceCache[$product->getId()]['regular_price'] = $this->priceCurrency->convertAndRound($price);
            }
        }

        return (float) number_format((float) $this->priceCache[$product->getId()]['regular_price'], 2);
    }
}
