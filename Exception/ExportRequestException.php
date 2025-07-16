<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Exception;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * An exception that is thrown when the export request to the Bloomreach API is unsuccessful
 */
class ExportRequestException extends LocalizedException
{
    /**
     * @var bool
     */
    private $isNeedUpdateRetryCounter;

    /**
     * @param Phrase $phrase
     * @param bool $isNeedUpdateRetryCounter
     * @param Exception|null $cause
     * @param int $code
     */
    public function __construct(
        Phrase $phrase,
        bool $isNeedUpdateRetryCounter = true,
        ?Exception $cause = null,
        $code = 0
    ) {
        $this->isNeedUpdateRetryCounter = $isNeedUpdateRetryCounter;
        parent::__construct($phrase, $cause, $code);
    }

    /**
     * Is need to update retry counter after failed attempt
     *
     * @return bool
     */
    public function isNeedUpdateRetryCounter(): bool
    {
        return $this->isNeedUpdateRetryCounter;
    }
}
