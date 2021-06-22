<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2017-08-24T15:48:57+00:00
 * File:          app/code/Xtento/OrderExport/Block/Adminhtml/Widget/Grid/Serializer.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml\Widget\Grid;

class Serializer extends \Magento\Backend\Block\Widget\Grid\Serializer
{
    protected function _afterToHtml($html)
    {
        $newJs = <<<EOT
serializerController.prototype.rowClick = function (grid, event) {
    if (typeof Event.findElement(event, 'a') == 'undefined') { // Dont call the checkbox method if the link or action column is clicked
        var trElement = Event.findElement(event, 'tr');
        var isInput   = Event.element(event).tagName == 'INPUT';
        if(trElement){
            var checkbox = Element.select(trElement, 'input');
            if(checkbox[0] && !checkbox[0].disabled){
                var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                this.grid.setCheckboxChecked(checkbox[0], checked);
            }
        }
        this.getOldCallback('row_click')(grid, event);
    }
};
new serializerController
EOT;
        $parentHtml = parent::_afterToHtml($html);
        $newHtml = str_replace('new serializerController', $newJs, $parentHtml);
        return $newHtml;
    }
}