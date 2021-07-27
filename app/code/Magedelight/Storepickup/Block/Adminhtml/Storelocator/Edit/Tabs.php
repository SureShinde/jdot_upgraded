<?php

/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category Magedelight
 * @package Magedelight_Storepickup
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Storepickup\Block\Adminhtml\Storelocator\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs
{

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('storelocator_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Store Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab(
            'time_info',
            [
            'label' => __('Time Schedule'),
            'title' => __('Time Schedule'),
            'content' => $this->getLayout()->createBlock(
                'Magedelight\Storepickup\Block\Adminhtml\Storelocator\Edit\Tab\Storetimes'
            )->toHtml(),
                ]
        );

        $this->addTab(
            'availabel_product',
            [
            'label' => __('Products of the store'),
            'title' => __('Products of the store'),
            'content' => $this->getLayout()->createBlock(
                'Magedelight\Storepickup\Block\Adminhtml\Storelocator\Edit\Tab\AvailabelProduct'
            )->toHtml(),
                ]
        );
        
        $this->addTab(
            'meta_info',
            [
            'label' => __('Meta Information'),
            'title' => __('Meta Information'),
            'content' => $this->getLayout()->createBlock(
                'Magedelight\Storepickup\Block\Adminhtml\Storelocator\Edit\Tab\Meta'
            )->toHtml(),
                ]
        );
        return parent::_beforeToHtml();
    }
}
