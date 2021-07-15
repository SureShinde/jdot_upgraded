<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Dhllabel\Controller\Adminhtml\Boxes;

class Index extends \Infomodus\Dhllabel\Controller\Adminhtml\Boxes
{
    /**
     * Account list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Infomodus_Dhllabel::dhllabel');
        $resultPage->getConfig()->getTitle()->prepend(__('DHL: Boxes'));
        $resultPage->addBreadcrumb(__('Infomodus'), __('Infomodus'));
        $resultPage->addBreadcrumb(__('Boxes'), __('Boxes'));
        return $resultPage;
    }
}
