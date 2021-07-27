<?php

/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category Magedelight
 * @package Magedelight_Storepickup
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Storepickup\Controller\Adminhtml\Storeholiday;

use Magedelight\Storepickup\Controller\Adminhtml\Storeholiday as StoreholidayController;
use Magento\Framework\Registry;
use Magedelight\Storepickup\Model\StoreholidayFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magedelight\Storepickup\Helper\Data as Storehelper;

class Edit extends StoreholidayController
{

    
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     *
     * @param Registry $registry
     * @param PageFactory $resultPageFactory
     * @param StoreholidayFactory $storeholidayFactory
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        PageFactory $resultPageFactory,
        StoreholidayFactory $storeholidayFactory,
        Context $context,
        Storehelper $storeHelper
    ) {
        $this->backendSession = $context->getSession();
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($registry, $storeholidayFactory, $context, $storeHelper);
    }

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        // 1. Get ID and create model
        //$id = $this->getRequest()->getParam('holiday_id');
        
        /** @var \Magedelight\Storepickup\Model\Storelocator $storeholiday */
        $storeholiday = $this->initStoreholiday();
        $store_id = $storeholiday->getStoreId();
        $id = $storeholiday->getHolidayId();

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magedelight_Storepickup::storelocator');
        $resultPage->getConfig()->getTitle()->set((__($storeholiday->getHolidayName())));

        // 2. Initial checking
        if ($id) {
            $storeholiday->load($id);
            if (!$storeholiday->getId()) {
                $this->messageManager->addError(__('This Holiday no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'magedelight_storepickup/*/edit',
                    [
                    'holiday_parent_id' => $storeholiday->getHolidayParentId(),
                    'store' => $store_id,
                    '_current' => true
                        ]
                );
                return $resultRedirect;
            }
        }
        if (!$storeholiday->getId()) {
            $resultPage->getLayout()->unsetChild('page.main.actions', 'storepickup.store.switcher');
        }
        // 3. Set entered data if was error when we do save
        $resultPage->getConfig()->getTitle()->prepend(__('Pages'));
        $resultPage->getConfig()->getTitle()
                ->prepend($storeholiday->getId() ? $storeholiday->getHolidayName() : __('New Holiday'));

        $data = $this->backendSession->getData('magedelight_storelocator_storeholiday_data', true);
        if (!empty($data)) {
            $storeholiday->setData($data);
        }
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Storepickup::storeholiday_save');
    }
}
