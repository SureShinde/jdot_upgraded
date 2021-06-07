<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Infomodus\Dhllabel\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save;

class ShipmentSaveActionAfter
{
    protected $_coreRegistry;
    protected $_context;
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;
    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $actionFlag;
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $url;
    protected $request;

    public function __construct(
        \Magento\Framework\Registry $registry,
        RequestInterface $request,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Backend\Model\UrlInterface $url
    )
    {
        $this->_coreRegistry = $registry;
        $this->request = $request;
        $this->redirect = $redirect;
        $this->actionFlag = $actionFlag;
        $this->url = $url;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function afterExecute(
        Save $subject,
        $result
    )
    {
        $request = $this->request;
        if ($this->_coreRegistry->registry('dhllabel_order_id') !== null) {
            $order_id = $this->_coreRegistry->registry('dhllabel_order_id');
            $shipment_id = $this->_coreRegistry->registry('dhllabel_shipment_id');
            $paramShipment = $request->getParam('shipment', null);

            if ($paramShipment !== null && isset($paramShipment['infomodus_dhl_label']) && $paramShipment['infomodus_dhl_label'] == 1) {
                return $this->redirect->redirect($subject->getResponse(), $this->url->getUrl('infomodus_dhllabel/items/edit', ['order_id' => $order_id, 'shipment_id' => $shipment_id, 'direction' => 'shipment', 'redirect_path' => 'shipment']));
            }
        }

        return $result;
    }
}
