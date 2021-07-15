<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Dhllabel\Controller\Adminhtml\Address;

class Save extends \Infomodus\Dhllabel\Controller\Adminhtml\Address
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            $data =[];
            try {
                $model = $this->address->create();
                $data = $this->getRequest()->getPostValue();
                $inputFilter = new \Zend_Filter_Input(
                    [],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong item is specified.'));
                    }
                }
                $model->setData($data);
                $session = $this->_getSession();
                $session->setPageData($model->getData());
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the item.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('infomodus_dhllabel/*/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('infomodus_dhllabel/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('infomodus_dhllabel/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('infomodus_dhllabel/*/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->logger->critical($e);
                $this->_getSession()->setPageData($data);
                $this->_redirect('infomodus_dhllabel/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('infomodus_dhllabel/*/');
    }
}
