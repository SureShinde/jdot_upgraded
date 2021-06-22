<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2016-05-29T13:38:59+00:00
 * File:          app/code/Xtento/OrderExport/Controller/Adminhtml/Profile/Duplicate.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Controller\Adminhtml\Profile;

class Duplicate extends \Xtento\OrderExport\Controller\Adminhtml\Profile
{
    /**
     * Duplicate action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        $id = (int)$this->getRequest()->getParam('id');
        $model = $this->profileFactory->create();
        $model->load($id);

        if ($id && !$model->getId()) {
            $this->messageManager->addErrorMessage(__('This profile does not exist anymore.'));
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        try {

            $profile = clone $model;
            $profile->setEnabled(0);
            $profile->setId(null);
            $profile->setLastModification(time());
            $profile->setLastExecution(null);
            $profile->save();

            $this->_session->setProfileDuplicated(1);
            $this->messageManager->addSuccessMessage(__('The profile has been duplicated. Please make sure to enable it.'));
            $resultRedirect->setPath('*/*/edit', ['id' => $profile->getId()]);
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }
}