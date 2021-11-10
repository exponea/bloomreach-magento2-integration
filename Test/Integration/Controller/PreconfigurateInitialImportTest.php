<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Test\Integration\Controller;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test Preconfigurate initial import
 */
class PreconfigurateInitialImportTest extends AbstractBackendController
{
    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * Preconfigurate initial import test
     *
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/order.php
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/customer.php
     *
     * @return void
     */
    public function testRunReconfiguration(): void
    {
        $this->dispatchImportReconfigurationRequest();
        $response = $this->jsonSerializer->unserialize((string) $this->getResponse()->getBody());
        $this->assertArrayHasKey('message', $response);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals(0, (int) $response['error']);
        $this->assertEquals(5, substr_count($response['message'], '.csv'));
    }

    /**
     * Dispatch RunInitialImportReconfiguration controller
     *
     * @return void
     */
    private function dispatchImportReconfigurationRequest(): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/bloomreach_engagement/import/RunInitialImportReconfiguration');
    }

    /**
     * Test set up
     *
     * @return void
     * @throws AuthenticationException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->jsonSerializer = $this->_objectManager->create(SerializerInterface::class);
    }
}
