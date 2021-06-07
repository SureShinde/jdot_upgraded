<?php

namespace Arpatech\CustomPrint\Block\Adminhtml\Shipment;
/**
 * Created by PhpStorm.
 * User: amber
 * Date: 26/04/17
 * Time: 5:29 PM
 */
class View extends \Magento\Shipping\Block\Adminhtml\View
{

    public function _construct()
    {
        $this->_blockGroup = 'Magento_Shipping';
        $this->_objectId = 'shipment_id';
        $this->_mode = 'view';
        $this->_controller = 'adminhtml';
        parent::_construct();
        if (!$this->getShipment()) {
            return;
        }
        $popup = "popWin('".$this->getPrintLabelUrl()."', '_blank')";
        $popupInvoice = "popWin('".$this->getInvoiceUrl()."', '_blank')";
        $popupCommInvoice = "popWin('".$this->getPrintCommercialInvoice()."', '_blank')";
        $this->buttonList->add(
            'printlabel',
            [
                'label' => __('Print Label'),
                'class' => 'save',
                'onclick' => $popup
            ]);
        $this->buttonList->add(
            'printinvoice',
            [
                'label' => __('Print Invoice'),
                'class' => 'save',
                'onclick' => $popupInvoice
            ]);
        $this->buttonList->add(
            'printinvoicecomm',
            [
                'label' => __('Print Commercial Invoice'),
                'class' => 'save',
                'onclick' => $popupCommInvoice
            ]
        );
    }

    public function getPrintLabelUrl()
    {
        return $this->getUrl('customshipping/shipment/print', ['shipment_id' => $this->getShipment()->getId()]);
    }
    public function getInvoiceUrl()
    {
        return $this->getUrl('customshipping/shipment/invoice', ['shipment_id' => $this->getShipment()->getId()]);
    }
    public function getPrintCommercialInvoice(){
        return $this->getUrl('customshipping/shipment/commercialinvoice', ['shipment_id' => $this->getShipment()->getId()]);
    }

    public function getLoadedShipment(){
        return $this->_coreRegistry->registry('shipment');
    }
    public function getLoadedCustomer(){
        return $this->_coreRegistry->registry('customer');
    }
    public function getLoadedOrder(){
        return $this->_coreRegistry->registry('order');
    }
    public function getPriceHelper(){
        return $this->_coreRegistry->registry('priceHelper');
    }
    public function getStoreAddress()
    {
        return $this->_scopeConfig->getValue(
            'carriers/mpcustomshipping/address',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getLogoUrl()
    {
        $folderName = \Magento\Config\Model\Config\Backend\Image\Logo::UPLOAD_DIR;
        $storeLogoPath = $this->_scopeConfig->getValue(
            'design/header/logo_src',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $path = $folderName . '/' . $storeLogoPath;
        $url = $this->_urlBuilder
                ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;

        return $url;
    }
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

}