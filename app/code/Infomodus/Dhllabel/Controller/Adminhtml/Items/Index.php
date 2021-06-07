<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Dhllabel\Controller\Adminhtml\Items;

class Index extends \Infomodus\Dhllabel\Controller\Adminhtml\Items
{
    /**
     * Items list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Infomodus_Dhllabel::dhllabel');
        $resultPage->getConfig()->getTitle()->prepend(__('DHL labels'));
        $resultPage->addBreadcrumb(__('DHL labels'), __('DHL labels'));
        $resultPage->addBreadcrumb(__('Labels'), __('Labels'));
        return $resultPage;
    }
}
