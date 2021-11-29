<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\File;

use Exception;

/**
 * This class is responsible for generating uniq file name
 */
class FileNameGenerator
{
    /**
     * Generate uniq file name
     *
     * @return string
     *
     * @throws Exception
     */
    public function execute(): string
    {
        return time() . '-' . bin2hex(random_bytes(10));
    }
}
