<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\DataMapperInterface;
use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;

/**
 * Render the value of entity field
 */
class FieldValueRendererResolver
{
    private const DEFAULT_RENDERER = 'default';

    /**
     * @var array
     */
    private $fieldValueRenderers;

    /**
     * @param array $fieldValueRenderers
     */
    public function __construct(array $fieldValueRenderers = [])
    {
        $this->fieldValueRenderers = $fieldValueRenderers;
    }

    /**
     * Render the value of entity field
     *
     * @param string $entityType
     * @param string $fieldCode
     * @param AbstractSimpleObject|AbstractModel $entity
     *
     * @return mixed
     * @throws ConfigurationMismatchException
     * @throws NoSuchEntityException
     */
    public function render(string $entityType, string $fieldCode, $entity)
    {
        return $this->getRenderer($entityType, $fieldCode)->render($entity, $fieldCode);
    }

    /**
     * Get instance of renderer for specified entity type and field code
     *
     * @param string $entityType
     * @param string $fieldCode
     *
     * @return RenderInterface
     * @throws ConfigurationMismatchException
     * @throws NoSuchEntityException
     */
    private function getRenderer(string $entityType, string $fieldCode): RenderInterface
    {
        $rendererName = isset($this->fieldValueRenderers[$entityType][$fieldCode]) ?
            $fieldCode : self::DEFAULT_RENDERER;

        if (!isset($this->fieldValueRenderers[$entityType]) ||
            !isset($this->fieldValueRenderers[$entityType][$rendererName])
        ) {
            throw new NoSuchEntityException(
                __(
                    'There is no such field value renderer "%1" for %2 entity for interface %3',
                    $rendererName,
                    $entityType,
                    DataMapperInterface::class
                )
            );
        }

        $renderer = $this->fieldValueRenderers[$entityType][$rendererName];

        if (!($renderer instanceof RenderInterface)) {
            throw new ConfigurationMismatchException(
                __(
                    'Field value renderer "%1" must implement interface %2',
                    get_class($renderer),
                    RenderInterface::class
                )
            );
        }

        return $renderer;
    }
}
