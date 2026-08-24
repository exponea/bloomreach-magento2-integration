<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Config\Backend;

use Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Catalog\FieldsMapper;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

/**
 * Allows select up to 20 options
 */
class ValidateSearchableFields extends Value
{
    /**
     * Checks for unique options.
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = array_filter((array)$this->getValue());

        if ($this->getFieldsetDataValue('enabled') !== '1') {
            if (!$value) {
                $this->_dataSaveAllowed = false;
            }

            return parent::beforeSave();
        }

        if (!$value) {
            throw new LocalizedException(__('Searchable fields cannot be empty.'));
        }

        if (count($value) > FieldsMapper::MAX_SEARCHABLE) {
            throw new LocalizedException(
                __(
                    'Maximum number of searchable fields exceeded. Max number: "%1"',
                    FieldsMapper::MAX_SEARCHABLE
                )
            );
        }

        return parent::beforeSave();
    }
}
