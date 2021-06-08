<?php
/**
 * @extension   Remmote_Facebookpixelremarketing
 * @author      Remmote
 * @copyright   2017 - Remmote.com
 * @descripion  Header extension info block
 */
namespace Remmote\Facebookpixelremarketing\Block\System\Config;

class Info extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * Override render function
     * @param AbstractElement $element
     * @return string
     * @author Remmote
     * @date   2017-07-18
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return
        '<fieldset style="border: 1px solid #ccc;">'.
            '<table>'.
                '<tr>'.
                    '<td style="padding:0;">'.
                        '<h2 style="margin-bottom: 1em;">Hey '.$this->_authSession->getUser()->getFirstname().
                            ', thanks for using our extension!</h2>'.
                        '<p style="margin: 0;">At remmote.com we are committed to deliver high quality development'.
                            ' services for Magento. If you have any question about our extension or need any help with'.
                            ' your online store, feel free to send us en email to info@remmote.com.'.
                            ' We will be happy to hear from you and we will reply you as soon as possible.</p>'.
                    '</td>'.
                '</tr>'.
            '</table>'.
        '</fieldset>';
    }

}
