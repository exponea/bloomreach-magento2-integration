<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\File;

/**
 * This class is responsible for generating uniq file name
 */
class FileNameGenerator
{
    /**
     * Generate uniq file name
     *
     * @return string
     */
    public function execute(): string
    {
        return (string) time() . '-' . uniqid();
    }
}
