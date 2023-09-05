<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Request\Entity;

use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Request\BuilderInterface;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Source\SourceGenerator;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;

/**
 * The class is responsible for building default request body
 */
class DefaultBuilder implements BuilderInterface
{
    /**
     * @var EntityType
     */
    private $entityType;

    /**
     * @var SourceGenerator
     */
    private $sourceGenerator;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @param EntityType $entityType
     * @param SourceGenerator $sourceGenerator
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        EntityType $entityType,
        SourceGenerator $sourceGenerator,
        ConfigProvider $configProvider
    ) {
        $this->entityType = $entityType;
        $this->sourceGenerator = $sourceGenerator;
        $this->configProvider = $configProvider;
    }

    /**
     * Builds default request body
     *
     * @param string $entityType
     * @param array $body
     *
     * @return array
     * @throws ConfigurationMismatchException
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws NotFoundException
     */
    public function build(string $entityType, array $body = []): array
    {
        $source = $this->sourceGenerator->generateEmpty($entityType);

        $body['name'] = $this->entityType->getEntityName($entityType);
        $body['trigger'] = [
            'trigger_type' => 'now'
        ];
        $body['active'] = true;
        $body['source'] = [
            'source_type' => 'url',
            'import_url' => $source->getFileUrl(),
            'use_socks_proxy' => $this->configProvider->isUseStaticIps(),
            'auth' => $this->getBasicHttpAuthSettings()
        ];
        $body['mapping'] = [
            'tz_info' => 'UTC',
            'column_mapping' => [
                'id_mappings' => [],
                'property_mappings' => $source->getPropertyMappings()
            ]
        ];
        $body['timestamp_column'] = null;

        return $body;
    }

    /**
     * Get Http Basic Auth settings
     *
     * @return array|null
     */
    private function getBasicHttpAuthSettings(): ?array
    {
        $username = $this->configProvider->getHttpBasicAuthUsername();
        $password = $this->configProvider->getHttpBasicAuthPassword();

        return $this->configProvider->isHttpBasicAuthEnabled() && $username && $password
            ? ['username' => $username, 'password' => $password]
            : null;
    }
}
