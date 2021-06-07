<?php
namespace Arpatech\Customshipping\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * @category   Arpatech
 * @package    Arpatech_Customshipping
 * @author     Arpatech@gmail.com
 * @website    http://www.Arpatech.com
 */
class Version extends \Magento\Config\Block\System\Config\Form\Field
{
    const EXTENSION_URL = '#';

    /**
     * @var \Arpatech\Customshipping\Helper\Data $helper
     */
    protected $_helper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Arpatech\Customshipping\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Arpatech\Customshipping\Helper\Data $helper
    ) {
        $this->_helper = $helper;
        parent::__construct($context);
    }


    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $extensionVersion   = $this->_helper->getExtensionVersion();
        $extensionTitle     = 'Custom Shipping';
        $versionLabel       = sprintf(
            '<a href="%s" title="%s" target="_blank">%s</a>',
            self::EXTENSION_URL,
            $extensionTitle,
            $extensionVersion
        );
        $element->setValue($versionLabel);

        return $element->getValue();
    }
}