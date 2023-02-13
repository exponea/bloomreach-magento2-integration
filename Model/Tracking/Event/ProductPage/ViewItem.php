<?php

/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */

declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Tracking\Event\ProductPage;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapperResolver;
use Bloomreach\EngagementConnector\Model\Tracking\Event\EventsInterface;
use Bloomreach\EngagementConnector\Model\Tracking\EventBuilderFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * The class is responsible for generating data for view item event
 */
class ViewItem implements ArgumentInterface, EventsInterface
{
    private const EVENT_NAME = 'view_item';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DataMapperResolver
     */
    private $dataMapperResolver;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var EventBuilderFactory
     */
    private $eventBuilderFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param DataMapperResolver $dataMapperResolver
     * @param RequestInterface $request
     * @param ProductRepositoryInterface $productRepository
     * @param EventBuilderFactory $eventBuilderFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        DataMapperResolver $dataMapperResolver,
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        EventBuilderFactory $eventBuilderFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->dataMapperResolver = $dataMapperResolver;
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->eventBuilderFactory = $eventBuilderFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Returns array with events
     *
     * @return array
     * @throws NoSuchEntityException
     * @throws ConfigurationMismatchException
     * @throws NotFoundException
     */
    public function getEvents(): array
    {
        $body = [];
        $product = $this->getCurrentProduct();

        if ($product) {
            $body = $this->dataMapperResolver->map($product, self::EVENT_NAME)->toArray();
        }

        return $this->eventBuilderFactory->create(['eventName' => self::EVENT_NAME, 'eventBody' => $body])->build();
    }

    /**
     * Returns current product
     *
     * @return ProductInterface|null
     */
    private function getCurrentProduct(): ?ProductInterface
    {
        $product = null;
        $productId = (int) $this->request->getParam('id');
        $defaultStoreId = (int) $this->storeManager->getDefaultStoreView()->getId();

        if ($productId) {
            try {
                $product = $this->productRepository->getById($productId, false, $defaultStoreId);
            } catch (NoSuchEntityException $e) {
                return null;
            }
        }

        return $product;
    }
}
