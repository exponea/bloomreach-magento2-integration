<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Event;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Model\AbstractModel;

/**
 * The class is responsible for rendering the language of website
 */
class Language implements RenderInterface
{
    /**
     * @var Resolver
     */
    private $localeResolver;

    /**
     * @param Resolver $localeResolver
     */
    public function __construct(Resolver $localeResolver)
    {
        $this->localeResolver = $localeResolver;
    }

    /**
     * Render the value of entity type field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        return substr((string) $this->localeResolver->getLocale(), 0, 2);
    }
}
