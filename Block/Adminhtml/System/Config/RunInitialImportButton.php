<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Block\Adminhtml\System\Config;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;
use Bloomreach\EngagementConnector\Service\InitialImport\ProcessStatus;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Button for running Initial Import in admin panel
 */
class RunInitialImportButton extends Button
{
    /**
     * Configurations required to enable the button
     */
    public const REQUIRED_FIELDS = [
        ConfigProvider::XPATH_API_KEY_ID,
        ConfigProvider::XPATH_API_SECRET,
        ConfigProvider::XPATH_API_TARGET,
        ConfigProvider::XPATH_PROJECT_TOKEN_ID,
        ConfigProvider::XPATH_CATALOG_IMPORT_ID,
        ConfigProvider::XPATH_VARIANTS_IMPORT_ID,
        ConfigProvider::XPATH_CUSTOMER_IMPORT_ID,
        ConfigProvider::XPATH_ORDER_IMPORT_ID,
        ConfigProvider::XPATH_ORDER_IMPORTLINE_ITEM_ID
    ];

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
            'is_import_in_progress' => $this->processStatus->execute(),
            'disable_button_after_click' => true
        ]);

        return $this->_toHtml();
    }
}
