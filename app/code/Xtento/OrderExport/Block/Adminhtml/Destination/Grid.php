<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2016-02-25T14:50:13+00:00
 * File:          app/code/Xtento/OrderExport/Block/Adminhtml/Destination/Grid.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml\Destination;

class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * @var \Xtento\OrderExport\Model\ProfileFactory
     */
    protected $profileFactory;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Xtento\OrderExport\Model\ProfileFactory $profileFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Xtento\OrderExport\Model\ProfileFactory $profileFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->profileFactory = $profileFactory;
    }

    protected function getProfile()
    {
        return $this->profileFactory->create()->load($this->getRequest()->getParam('id'));
    }

    public function getSelectedDestinations()
    {
        $array = explode("&", $this->getProfile()->getDestinationIds());
        return $array;
    }

    protected function getFormMessages()
    {
        $formMessages = [
            [
                'type' => 'notice',
                'message' => __('Export destinations control where exported files are sent to. Set up local directory, FTP, SFTP, etc. destinations and enable them in the export profiles "Export Destinations" tab.')
            ]
        ];
        return $formMessages;
    }

    protected function _toHtml()
    {
        if ($this->getRequest()->getParam('ajax')) {
            return parent::_toHtml();
        }
        return $this->_getFormMessages() . parent::_toHtml();
    }

    protected function _getFormMessages()
    {
        $html = '<div id="messages"><div class="messages">';
        foreach ($this->getFormMessages() as $formMessage) {
            $html .= '<div class="message message-' . $formMessage['type'] . ' ' . $formMessage['type'] . '"><div>' . $formMessage['message'] . '</div></div>';
        }
        $html .= '</div></div>';
        return $html;
    }
}