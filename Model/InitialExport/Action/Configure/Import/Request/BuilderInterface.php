<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Request;

use Magento\Framework\Exception\LocalizedException;

/**
 * Builds request body for import creation
 */
interface BuilderInterface
{
    /**
     * Builds request body for import creation
     *
     * @param string $entityType
     * @param array $body
     *
     * @return array
     * @throws LocalizedException
     */
    public function build(string $entityType, array $body = []): array;
}
