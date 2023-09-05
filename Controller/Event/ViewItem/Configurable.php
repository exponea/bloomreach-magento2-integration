<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Controller\Event\ViewItem;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\ViewItemEvent;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapperResolver;
use Bloomreach\EngagementConnector\Model\Tracking\EventBuilderFactory;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

/**
 * The class is responsible for generating view item event for configurable product option
 */
class Configurable implements ActionInterface, HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var DataMapperResolver
     */
    private $dataMapperResolver;

    /**
     * @var EventBuilderFactory
     */
    private $eventBuilderFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param DataMapperResolver $dataMapperResolver
     * @param EventBuilderFactory $eventBuilderFactory
     * @param RequestInterface $request
     * @param JsonFactory $jsonFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        DataMapperResolver $dataMapperResolver,
        EventBuilderFactory $eventBuilderFactory,
        RequestInterface $request,
        JsonFactory $jsonFactory,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->dataMapperResolver = $dataMapperResolver;
        $this->eventBuilderFactory = $eventBuilderFactory;
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
    }

    /**
     * Returns variant data for configurable product
     *
     * @return Json
     */
    public function execute()
    {
        $productId = (int) $this->request->getParam('product_id');

        if (!$productId) {
            return $this->jsonFactory->create()->setData(
                [
                    'errorMessage' => __('Wrong Request Data. Product Id is required'),
                    'event' => []
                ]
            );
        }

        try {
            $product = $this->productRepository->getById($productId);
            $product->setVariantId($productId);
            $body = $this->dataMapperResolver->map($product, ViewItemEvent::ENTITY_TYPE)->toArray();
            $event = $this->eventBuilderFactory->create(
                [
                    'eventName' => ViewItemEvent::ENTITY_TYPE,
                    'eventBody' => $body
                ]
            )->build();
            $response = [
                'errorMessage' => '',
                'event' => $event
            ];
        } catch (Exception $exception) {
            $this->logger->error(
                __('An error occurred while generating event data. Error: %1', $exception->getMessage())
            );
            $response = [
                'errorMessage' => __('An error occurred while generating event data.'),
                'event' => []
            ];
        }

        return $this->jsonFactory->create()->setData($response);
    }
}
