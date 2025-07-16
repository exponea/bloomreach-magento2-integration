<?php

declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Test\Integration\Controller;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Credential validator test
 * @SuppressWarnings(PHPMD)
 */
class AccessCredentialsValidatorTest extends AbstractBackendController
{
    /**
     * @var string
     */
    public $uri = 'backend/bloomreach_engagement/validator/AccessCredentials';

    /**
     * @var string
     */
    public $resource = 'Bloomreach_EngagementConnector::bloomreach_engagement_config';

    /**
     * @var string
     */
    public $httpMethod = HttpRequest::METHOD_POST;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * Test wrong credentials
     *
     * @magentoConfigFixture default/bloomreach_engagement/general/api_target https://api-engagement.bloomreach.com
     * @magentoConfigFixture default/bloomreach_engagement/general/api_key_id 12345
     * @magentoConfigFixture default/bloomreach_engagement/general/api_secret 12345
     * @magentoConfigFixture default/bloomreach_engagement/general/project_token_id 12345
     *
     * @return void
     */
    public function testWrongCredentials(): void
    {
        $this->dispatchValidateAccessCredentialsRequest();
        $response = $this->jsonSerializer->unserialize((string) $this->getResponse()->getBody());
        $this->assertArrayHasKey('message', $response);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals(1, (int) $response['error']);
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

    /**
     * Dispatch AccessCredentials controller
     *
     * @return void
     */
    private function dispatchValidateAccessCredentialsRequest(): void
    {
        $this->getRequest()->setMethod($this->httpMethod);
        $this->dispatch($this->uri);
    }
}
