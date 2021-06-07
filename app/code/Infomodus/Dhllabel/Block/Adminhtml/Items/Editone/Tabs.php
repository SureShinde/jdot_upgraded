<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */
namespace Infomodus\Dhllabel\Block\Adminhtml\Items\Editone;

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
        $this->setId('infomodus_dhllabel_items_editone_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('First step'));
    }
}
