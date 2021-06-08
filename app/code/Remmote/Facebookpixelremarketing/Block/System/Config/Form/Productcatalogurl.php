<?php
/**
 * @extension   Remmote_Facebookpixelremarketing
 * @author      Remmote
 * @copyright   2017 - Remmote.com
 * @descripion  Facebook Product Catalog url block
 */
namespace Remmote\Facebookpixelremarketing\Block\System\Config\Form;

class Productcatalogurl extends \Magento\Config\Block\System\Config\Form\Field
{
    const URL_TEMPLATE = 'system/config/field/productcatalogurl.phtml';

    /**
     * @var \Remmote\Facebookpixelremarketing\Helper\Data
     */
    public $helper;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    public $directoryList;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Remmote\Facebookpixelremarketing\Helper\Data $helper
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Remmote\Facebookpixelremarketing\Helper\Data $helper,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->directoryList = $directoryList;
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
            $this->setTemplate(self::URL_TEMPLATE);
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
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @author Remmote
     * @date   2017-07-18
     */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        //Get current website id from URL. If it is empty, it will be 0 = default
        $websiteId   = (int) $this->getRequest()->getParam('website', 0);

        //Get Magento websites
        $websites    = $this->_storeManager->getWebsites();

        //Check if there are more than 1 website and if website id is present in URL
        if (count($websites) > 1 && !$websiteId) {
            return '<p style="color: #eb5202;">You have more one than website configured on your Magento installation.'.
                ' Please select the website from which you want to export your product catalog.</p>';
        }

        //Load website and base url
        $website = $this->_storeManager->getWebsite($websiteId);
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).
            'facebook_productcatalog/';

        $productCatalogUrl = '';
        foreach ($website->getStores() as $store) {
            if (!$store->isActive()) {
                continue;
            }

            // Build csv file name
            $fileName    = 'products_'.$website->getCode().'_'.$store->getCode().'.csv';

            // Check if each file exist
            $catalogPath = $this->directoryList->getPath('media') . DIRECTORY_SEPARATOR
                .'facebook_productcatalog'. DIRECTORY_SEPARATOR . $fileName;
            if (!file_exists($catalogPath)) {
                return '<p style="color: #eb5202;">No product catalog found. Please configure the extension'.
                    ' according to your preferred settings and click the button "Export Now" to generate your'.
                    ' Product Catalog URL.</p>';
            }

            // Build html format to show on config page
            $productCatalogUrl .= '<strong style="color: #eb5202;">'.$store->getName().'</strong><br>'.
                '<span style="color: #eb5202;">'.$baseUrl.$fileName.'</span><br>'.
                '<a href="'.$baseUrl.$fileName.'?'.time().'" style="color:#666;">Download the .csv file</a>'.
                '<br><br>';
        }
        return $productCatalogUrl;
    }

}