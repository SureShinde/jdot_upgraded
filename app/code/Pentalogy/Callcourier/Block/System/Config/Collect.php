<?php
 namespace Pentalogy\Callcourier\Block\System\Config;
 use Magento\Backend\Block\Template\Context;
 use Magento\Config\Block\System\Config\Form\Field;
 use Magento\Framework\Data\Form\Element\AbstractElement;
 use Magento\Framework\App\Config\ScopeConfigInterface;

 class Collect extends Field
 {
     protected $_template = 'Pentalogy_Callcourier::system/config/collect.phtml';
     protected $_scopeConfig;
     public function __construct(Context $context,
                                 ScopeConfigInterface $scopeConfig,
                                 array $data = []){
         $this->_scopeConfig = $scopeConfig;
         parent::__construct($context, $data);
     }

     public function render(AbstractElement $element)
     {
         $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
         return parent::render($element);
     }

     /**
      * Return element html
      *
      * @param  AbstractElement $element
      * @return string
      */
     protected function _getElementHtml(AbstractElement $element)
     {
         return $this->_toHtml();
     }

     /**
      * Return ajax url for collect button
      *
      * @return string
      */
     public function getAjaxUrl()
     {
         return $this->getUrl('pentalogy_callcourier/system_config/shipperareacollect');
     }

     public function getAreaId(){
         return $this->_scopeConfig->getValue('callcouriertabsection/callcouriergroup/shipperorigin',
             \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
     }

     /**
      * Generate collect button html
      *
      * @return string
      */
     public function getButtonHtml()
     {
         $button = $this->getLayout()->createBlock(
             'Magento\Backend\Block\Widget\Button'
         )->setData(
             [
                 'id' => 'city_area',
                 'label' => __('Get City Area'),
             ]
         );

         return $button->toHtml();
     }
 }
