<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Block\Adminhtml\System\Config;

use Bloomreach\EngagementConnector\Service\InitialImport\ProcessStatus;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Phrase;

/**
 * Button for running Initial Import in admin panel
 */
class RunInitialImportButton extends Button
{
    /**
     * @var ProcessStatus
     */
    private $processStatus;

    /**
     * @param Context $context
     * @param ProcessStatus $processStatus
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProcessStatus $processStatus,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->processStatus = $processStatus;
    }

    /**
     * Get the button and scripts contents
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        parent::_getElementHtml($element);
        $this->addData([
            'is_import_in_progress' => $this->processStatus->execute()
        ]);

        return $this->_toHtml();
    }
}
