<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2016-01-08T13:39:10+00:00
 * File:          app/code/Xtento/OrderExport/Block/Adminhtml/Profile/Grid/Renderer/Configuration.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml\Profile\Grid\Renderer;

class Configuration extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render profile configuration
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $configuration = [];
        $configuration['Cronjob Export'] = ($row->getCronjobEnabled()) ? __('Enabled') : __('Disabled');
        $configuration['Event Export'] = ($row->getEventObservers() !== '') ? __('Enabled') : __('Disabled');
        if (!empty($configuration)) {
            $configurationHtml = '';
            foreach ($configuration as $key => $value) {
                $configurationHtml .= __($key).': <i>'.$value.'</i><br/>';
            }
            return $configurationHtml;
        } else {
            return '---';
        }
    }
}
