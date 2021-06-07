<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Dhllabel\Controller\Adminhtml\Conformity;

class Edit extends \Infomodus\Dhllabel\Controller\Adminhtml\Conformity
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->conformity->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));
                $this->_redirect('infomodus_dhllabel/*');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_getSession()->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_infomodus_dhllabel_conformity', $model);
        $this->_initAction();
        $this->_view->getLayout()->getBlock('conformity_conformity_edit');
        $this->_view->renderLayout();
    }
}
