<?php
/**
 * @extension   Remmote_Facebookpixelremarketing
 * @author      Remmote
 * @copyright   2017 - Remmote.com
 * @descripion  Facebook Product Catalog export button block
 */
namespace Remmote\Facebookpixelremarketing\Block\System\Config\Form;

class Button extends \Magento\Config\Block\System\Config\Form\Field
{
    const BUTTON_TEMPLATE = 'system/config/field/button.phtml';
    
    /**
     * @var \Remmote\Facebookpixelremarketing\Helper\Data
     */
    public $helper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Remmote\Facebookpixelremarketing\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Remmote\Facebookpixelremarketing\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Set template to itself
     * @return $this
     * @author Remmote
     * @date   2017-07-18
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(self::BUTTON_TEMPLATE);
        }
        return $this;
    }

    /**
     * Override _renderInheritCheckbox function. Don't return anything to delete 'use default' checkbox
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function _renderInheritCheckbox(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Function must be empty to override parent function
    }

    /**
     * Return ajax url for button
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function getAjaxExportcatalogUrl()
    {
        return $this->getUrl('facebookpixelremarketing/productcatalog/export');
    }

    /**
     * Get website id from URL for button
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function getWebsiteId()
    {
        return (int) $this->getRequest()->getParam('website', 0);
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @author Remmote
     * @date   2017-07-18
     */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        //Prepare button properties
        $buttonProperties = [
            'id'            => 'exportcatalog',
            'button_label'  => __('Export Now')
        ];

        //Get current website id from URL
        $websiteId = $this->getWebsiteId();

        // if (!$this->helper->isEnabled($websiteId)) {
        //     $buttonProperties['disabled'] = 'disabled';
        //     $this->addData($buttonProperties);
        //     return $this->_toHtml() .
        //         '<p style="color: #eb5202;">Facebook Pixel Remarketing must be enabled to use this functionality.</p>';
        // }

        //Get Magento websites
        $websites = $this->_storeManager->getWebsites();

        //Check if there are more than 1 website and if website id is present in URL
        if (count($websites) > 1 && !$websiteId) {
            $buttonProperties['disabled'] = 'disabled';
            $this->addData($buttonProperties);
            return $this->_toHtml() . '<p style="color: #eb5202;">It seems that you have more than one website'.
                ' configured on your Magento installation. Please select the website from which you need to export'.
                ' the product catalog (dropdown menu in the left site of this screen).</p>';
        }

        //Add onclick event to button
        $buttonProperties['onclick'] = 'javascript:export_catalog(); return false;';
        $this->addData($buttonProperties);

        return $this->_toHtml() . '<p class="note"><span>Click here to export your product catalog and generate'.
            ' the link to access your product catalog file. <br>You might need to reindex your products if your'.
            ' CSV file is empty.</span></p>';
    }

}