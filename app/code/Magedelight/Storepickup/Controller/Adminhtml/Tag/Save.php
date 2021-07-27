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

use Magento\Framework\Registry;
use Magedelight\Storepickup\Controller\Adminhtml\Tag;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magedelight\Storepickup\Model\TagFactory;
use Magento\Backend\Model\Session;
use Magento\Backend\App\Action\Context;
use Magedelight\Storepickup\Model\Storelocator\Image as ImageModel;
use Magento\Framework\Exception\LocalizedException;
use Magedelight\Storepickup\Model\Upload;
use Magento\Backend\Helper\Js as JsHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magedelight\Storepickup\Helper\Data as Storehelper;

class Save extends Tag
{

    /**
     * storelocator factory
     * @var
     */
    protected $storelocatorFactory;

    /**
     * image model
     *
     * @var \Magedelight\Storepickup\Model\Storelocator\Image
     */
    protected $imageModel;

    /**
     * file model
     *
     * @var \Magedelight\Storepickup\Model\Storelocator\File
     */
    protected $fileModel;

    /**
     * upload model
     *
     * @var \Magedelight\Storepickup\Model\Upload
     */
    protected $uploadModel;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;

    /**
     *
     * @param JsHelper $jsHelper
     * @param Registry $registry
     * @param ImageModel $imageModel
     * @param Upload $uploadModel
     * @param StorelocatorFactory $storelocatorFactory
     * @param Context $context
     */
    public function __construct(
        JsHelper $jsHelper,
        Registry $registry,
        ImageModel $imageModel,
        Upload $uploadModel,
        TagFactory $storelocatorFactory,
        Context $context,
        Storehelper $storeHelper
    ) {
        $this->jsHelper = $jsHelper;
        $this->imageModel = $imageModel;
        $this->uploadModel = $uploadModel;
        parent::__construct($registry, $storelocatorFactory, $context, $storeHelper);
    }

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('tag_');
        $stores = $this->getRequest()->getPost('links');
        $resultRedirect = $this->resultRedirectFactory->create();
        $data['store_ids'] = $stores['tagstores'];

        if ($data) {
            $defaultstoreid = $this->storeHelper->_getDefaultStoreId();
            $tagparentId = isset($data['tag_parent_id']) ? (int)$data['tag_parent_id'] : null;
            $md_store_id = isset($data['store_id']) ? $data['store_id'] : null;
            $switch_id = isset($data['store_id']) ? $data['store_id'] : $defaultstoreid;
            if ($defaultstoreid != $md_store_id && isset($tagparentId)) {
                $tag = $this->tagFactory
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter('tag_parent_id', $tagparentId)
                            ->addFieldToFilter('store_id', $md_store_id)
                            ->getFirstItem();
                $tagid = $tag['tag_id'];
                if (isset($tagid)) {
                    $tag->load($tagid);
                    $tag->setData($data);
                    $tag->setStorelocatorId($tagid);
                } else {
                    $data['tag_id'] = null;
                    $tag->setData($data);
                    $tag->setStoreId($md_store_id);
                    $tag->setTagParentId($tagparentId);
                }
            } else {
                $tag = $this->initTag();
                $data['tag_parent_id'] = $tagparentId;
                $tag->setStoreId($defaultstoreid);
                $tag->setData($data);
            }

            
            try {
                $storeimage = $this->uploadModel->uploadFileAndGetName('tag_icon', $this->imageModel->getBaseDir(), $data);
                $tag->setTagIcon($storeimage);
                $tag->save();
                if ($tagparentId == null) {
                    $saved_id = $tag->getTagId();
                    $tag->setTagParentId($saved_id);
                    $tag->save();
                }
                $this->messageManager->addSuccess(__('The tag has been saved.'));
                $this->_getSession()->setMagedelightStorelocatorStorelocatorData(false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        '*/*/edit',
                        [
                        'tag_parent_id' => $tag->getTagParentId(),
                        'store' => $switch_id,
                        '_current' => true
                            ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Tag.'));
            }

            $this->_getSession()->setMagedelightStorelocatorStorelocatorData($data);
            $resultRedirect->setPath(
                '*/*/edit',
                [
                'tag_parent_id' => $tag->getTagParentId(),
                'store' => $switch_id,
                '_current' => true
                    ]
            );
            return $resultRedirect;
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Storepickup::tag_save');
    }
}
