<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Exception;

use Magento\Framework\Exception\LocalizedException;

/**
 * An exception is thrown if a request to Bloomreach returns a status other than 200
 */
class BadRequestException extends LocalizedException
{
}
