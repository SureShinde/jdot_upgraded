<?php
/**
 * @extension   Remmote_Facebookpixelremarketing
 * @author      Remmote
 * @copyright   2017 - Remmote.com
 * @descripion  Facebook Scheduled Recurring Uploads - Last Export Time
 */
namespace Remmote\Facebookpixelremarketing\Block\System\Config\Form;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Lastexporttime extends \Magento\Config\Block\System\Config\Form\Field
{

    protected $_collectionFactory;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory)
    {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
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

    }


    protected function _getElementHtml(AbstractElement $element)
    {
        $websiteId   = (int) $this->getRequest()->getParam('website', 0);
        $collection = $this->_collectionFactory->create();

        $collection->addScopeFilter($websiteId ? \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES : 'default',$websiteId ? $websiteId : '', 'remmote_facebookpixelremarketing');
        $collection->load();
        foreach ($collection->getItems() as $data) {
            if ($data->getPath() == \Remmote\Facebookpixelremarketing\Helper\Data::TIME_LASTEXPORT) {
                $config[$data->getPath()] = [
                    'path' => $data->getPath(),
                    'value' => $data->getValue(),
                    'config_id' => $data->getConfigId(),
                ];
                $element->setValue($data->getValue());
                break;
            }
        }

        return $element->getValue();
    }

}
