<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Dhllabel\Controller\Adminhtml\Items;

class Edit extends \Infomodus\Dhllabel\Controller\Adminhtml\Items
{
    public function execute()
    {
        try {
            $order_id = $this->getRequest()->getParam('order_id');
            $shipment_id = $this->getRequest()->getParam('shipment_id', null);
            $type = $this->getRequest()->getParam('direction');
            $model = [];
            if ($order_id) {
                $order = $this->orderRepository->get($order_id);
                if ($order) {
                    $intermediate = $this->_handy->intermediate($order, $type, $shipment_id);
                    if (true === $intermediate) {
                        $model['handy'] = $this->_handy;
                        $model['object'] = $this->_objectManager;
                    } else {
                        $this->messageManager->addErrorMessage(__('Error 10001.'));
                        $this->_redirect('infomodus_dhllabel/*');
                        return;
                    }
                } else {
                    $this->messageManager->addErrorMessage(__('Order ID is incorrect.'));
                    $this->_redirect('infomodus_dhllabel/*');
                    return;
                }
            } else {
                $this->messageManager->addErrorMessage(__('Order ID is required.'));
                $this->_redirect('infomodus_dhllabel/*');
                return;
            }
            // set entered data if was error when we do save
            $data = $this->_getSession()->getPageData(true);
            if (!empty($data)) {
                $model['data'] = $data;
            }
            $this->_coreRegistry->register('current_infomodus_dhllabel_items', $model);
            $this->_initAction();
            $this->_view->getLayout()->getBlock('items_items_edit');
            $this->_view->renderLayout();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('infomodus_dhllabel/*');
        }
    }
}
