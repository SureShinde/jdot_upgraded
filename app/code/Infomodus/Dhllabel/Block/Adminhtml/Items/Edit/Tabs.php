<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */
namespace Infomodus\Dhllabel\Block\Adminhtml\Items\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('infomodus_dhllabel_items_edit_tabs');
        $this->setDestElementId('edit_form');
        switch($this->getRequest()->getParam('direction')){
            case 'refund': $label = 'RMA(return) DHL label';
                break;
            case 'invert': $label = 'Invert DHL label';
                break;
            default: $label = 'Shipping DHL label';
        }
        $this->setTitle(__($label));
    }
}
