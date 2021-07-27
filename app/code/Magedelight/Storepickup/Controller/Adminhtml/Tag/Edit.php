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

namespace Magedelight\Storepickup\Controller\Adminhtml\Tag;

use Magedelight\Storepickup\Controller\Adminhtml\Tag as TagController;
use Magento\Framework\Registry;
use Magedelight\Storepickup\Model\TagFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magedelight\Storepickup\Helper\Data as Storehelper;

class Edit extends TagController
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     *
     * @param Registry $registry
     * @param PageFactory $resultPageFactory
     * @param StorelocatorFactory $storelocatorFactory
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        PageFactory $resultPageFactory,
        TagFactory $tagFactory,
        Context $context,
        Storehelper $storeHelper
    ) {
        $this->backendSession = $context->getSession();
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($registry, $tagFactory, $context, $storeHelper);
    }

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        // 1. Get ID and create model
        //$id = $this->getRequest()->getParam('tag_id');
        /** @var \Magedelight\Storepickup\Model\Storelocator $storelocator */
        $tag = $this->initTag();
        $store_id = $tag->getStoreId();
        $id = $tag->getTagId();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magedelight_Storepickup::tag');
        $resultPage->getConfig()->getTitle()->set((__('Tag')));

        // 2. Initial checking
        if ($id) {
            $tag->load($id);
            if (!$tag->getId()) {
                $this->messageManager->addError(__('This tag no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'magedelight_storepickup/*/edit',
                    [
                    'tag_parent_id' => $storelocator->getStoreParentId(),
                    'store' => $store_id,
                    '_current' => true
                        ]
                );
                return $resultRedirect;
            }
        }

        // 3. Set entered data if was error when we do save
        if (!$tag->getId()) {
            $resultPage->getLayout()->unsetChild('page.main.actions', 'storepickup.store.switcher');
        }
        $resultPage->getConfig()->getTitle()->prepend(__('Pages'));
        $resultPage->getConfig()->getTitle()
                ->prepend($tag->getId() ? $tag->getStorename() : __('New Tag'));

        $data = $this->backendSession->getData('magedelight_store_tag_data', true);
        
        if (!empty($data)) {
            $tag->setData($data);
        }
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Storepickup::tag_save');
    }
}
