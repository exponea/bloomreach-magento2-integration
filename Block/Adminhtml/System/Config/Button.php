<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Fronted model for rendering the initial import buttons
 */
class Button extends Field
{
    /**
     * Path to catalog import id config
     */
    private const XPATH_CATALOG_IMPORT_ID = 'bloomreach_engagement/general/catalog_import_id';

    /**
     * Path to variants import id config
     */
    private const XPATH_VARIANTS_IMPORT_ID = 'bloomreach_engagement/general/variants_import_id';

    /**
     * Path to customer import id config
     */
    private const XPATH_CUSTOMER_IMPORT_ID = 'bloomreach_engagement/general/customer_import_id';

    /**
     * Path to order import id config
     */
    private const XPATH_ORDER_IMPORT_ID = 'bloomreach_engagement/general/order_import_id';

    /**
     * Path to order import line item id config
     */
    private const XPATH_ORDER_IMPORTLINE_ITEM_ID = 'bloomreach_engagement/general/order_importline_item_id';

    /**
     * @var string
     */
    protected $_template = 'Bloomreach_EngagementConnector::system/config/button.phtml';

    /**
     * Unset scope and label
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope();
        $element->unsLabel();

        return parent::render($element);
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
        $originalData = $element->getOriginalData();
        $this->addData([
            'button_label' => $originalData['button_label'],
            'button_url' => $this->getUrl($originalData['button_url'], ['_current' => true]),
            'html_id' => $element->getHtmlId(),
            'is_enabled' => $this->isEnabled()
        ]);

        return $this->_toHtml();
    }

    /**
     * Checks whether is component enabled
     *
     * @return bool
     */
    protected function isEnabled(): bool
    {
        return $this->_scopeConfig->getValue(self::XPATH_CATALOG_IMPORT_ID) &&
            $this->_scopeConfig->getValue(self::XPATH_VARIANTS_IMPORT_ID) &&
            $this->_scopeConfig->getValue(self::XPATH_CUSTOMER_IMPORT_ID) &&
            $this->_scopeConfig->getValue(self::XPATH_ORDER_IMPORT_ID) &&
            $this->_scopeConfig->getValue(self::XPATH_ORDER_IMPORTLINE_ITEM_ID);
    }
}
