<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Block\Adminhtml\System\Config;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Fronted model for rendering the button
 */
class Button extends Field
{
    /**
     * Configurations required to enable the button
     */
    public const REQUIRED_FIELDS = [];

    /**
     * Map config path to system.xml path
     */
    public const CONFIG_MAPPING = [
        ConfigProvider::XPATH_CUSTOMER_IMPORT_ID => 'bloomreach_engagement/imports/customer_import_id',
        ConfigProvider::XPATH_CATALOG_IMPORT_ID => 'bloomreach_engagement/imports/catalog_import_id',
        ConfigProvider::XPATH_VARIANTS_IMPORT_ID => 'bloomreach_engagement/imports/variants_import_id',
        ConfigProvider::XPATH_ORDER_IMPORT_ID => 'bloomreach_engagement/imports/order_import_id',
        ConfigProvider::XPATH_ORDER_IMPORTLINE_ITEM_ID => 'bloomreach_engagement/imports/order_importline_item_id'
    ];

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
            'is_enabled' => $this->isEnabled(),
            'required_fields_selectors' => $this->getRequiredFieldsSelectors(),
            'disable_button_after_click' => false
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
        foreach (static::REQUIRED_FIELDS as $requireField) {
            if (!$this->_scopeConfig->getValue($requireField)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get required fields selectors separated by comma #field_id1,#field_id2
     *
     * @return string
     */
    protected function getRequiredFieldsSelectors(): string
    {
        $result = [];

        foreach (static::REQUIRED_FIELDS as $requiredField) {
            $requiredField = static::CONFIG_MAPPING[$requiredField] ?? $requiredField;
            $result[] = sprintf('%s%s', '#', str_replace('/', '_', $requiredField));
        }

        return implode(',', $result);
    }
}
