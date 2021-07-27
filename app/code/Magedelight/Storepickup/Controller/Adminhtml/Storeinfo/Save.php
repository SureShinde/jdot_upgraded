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

namespace Magedelight\Storepickup\Controller\Adminhtml\Storeinfo;

use Magento\Framework\Registry;
use Magedelight\Storepickup\Controller\Adminhtml\Storelocator;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magedelight\Storepickup\Model\StorelocatorFactory;
use Magento\Backend\Model\Session;
use Magento\Backend\App\Action\Context;
use Magedelight\Storepickup\Model\Storelocator\Image as ImageModel;
use Magento\Framework\Exception\LocalizedException;
use Magedelight\Storepickup\Model\Upload;
use Magento\Backend\Helper\Js as JsHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magedelight\Storepickup\Helper\Data as Storehelper;

class Save extends Storelocator
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
        StorelocatorFactory $storelocatorFactory,
        Context $context,
        StoreManagerInterface $storeManager,
        Storehelper $storeHelper,
        \Magento\Framework\Serialize\Serializer\Json $serialize
    ) {
        $this->jsHelper = $jsHelper;
        $this->imageModel = $imageModel;
        $this->uploadModel = $uploadModel;
        $this->_storeManager = $storeManager;
        $this->serialize = $serialize;
        parent::__construct($registry, $storelocatorFactory, $context, $storeHelper);
    }

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        //'storelocator'
        $data = $this->getRequest()->getPost('storelocator');
        $timedata = $this->getRequest()->getPost();
        $resultRedirect = $this->resultRedirectFactory->create();

        if (count($timedata['telephone']) > 1) {
            $data["telephone"] = implode(':', $timedata['telephone']);
        } else {
            $data["telephone"] = $timedata['telephone'][0];
        }

        if (!empty($timedata['storelocator']['address'][1])) {
            $data["address"] = implode('\n', $timedata['storelocator']['address']);
        } else {
            $data["address"] = $timedata['storelocator']['address'][0];
        }

        if (!empty($timedata['storetime'])) {
            $storetime = $timedata['storetime'];

            foreach ($timedata['storetime'] as $key => $value) {
                if ($value['delete'] == 1) {
                    unset($storetime[$key]);
                }
            }
            $data["storetime"] = $this->serialize->serialize($storetime);
        }


        //$timedata = $this->getRequest()->getPost();
        if (count($timedata['rule']['conditions'])>1) {
            $conditionsData['conditions'] = $timedata['rule']['conditions'];
            $conditionsModel = $this->_objectManager->create('Magedelight\Storepickup\Model\Condition');
            $conditionDataArray = $conditionsModel->dataConvter($conditionsData);
            $data["conditions_serialized"] = $this->serialize->serialize($conditionDataArray);

            $conditionsModel->loadPost($conditionsData);
            $productIds_conditions = $conditionsModel->getListProductIdsInRule();
            if (!empty($productIds_conditions)) {
                $productIds_conditions = implode(",", $productIds_conditions);
            } else {
                $productIds_conditions = null;
            }
        } else {
            $productIds_conditions = null;
        }
        $data['product_ids'] = $productIds_conditions;

        if ($data) {
            $defaultstoreid = $this->storeHelper->_getDefaultStoreId();
            $storelocatorId = isset($data['store_parent_id']) ? (int)$data['store_parent_id'] : null;
            
            $md_store_id = isset($data['store_id']) ? $data['store_id'] : null;
            $switch_id = isset($data['store_id']) ? $data['store_id'] : $defaultstoreid;
            
            if ($defaultstoreid != $md_store_id && isset($storelocatorId)) {
                $storelocator = $this->storelocatorFactory
                                     ->create()
                                     ->getCollection()
                                     ->addFieldToFilter('store_parent_id', $storelocatorId)
                                     ->addFieldToFilter('store_id', $md_store_id)
                                     ->getFirstItem();
                $slocatorid = $storelocator['storelocator_id'];
                if (isset($slocatorid)) {
                    $storelocator->load($slocatorid);
                    $storelocator->setData($data);
                    $storelocator->setStorelocatorId($slocatorid);
                } else {
                    $data['storelocator_id'] = null;
                    $storelocator->setData($data);
                    $storelocator->setStoreId($md_store_id);
                    $storelocator->setStoreParentId($storelocatorId);
                }
            } else {
                $storelocator = $this->initStorelocator();
                $data['store_parent_id'] = $storelocatorId;
                $storelocator->setStoreId($defaultstoreid);
                $storelocator->setData($data);
            }
            
            
            try {
                $storeimage = $this->uploadModel->uploadFileAndGetName('storeimage', $this->imageModel->getBaseDir(), $data);
                $storelocator->setStoreimage($storeimage);
                $storelocator->save();
                
                if ($storelocatorId == null) {
                    $saved_id = $storelocator->getStorelocatorId();
                    $storelocator->setStoreParentId($saved_id);
                    $storelocator->save();
                }
                
                $this->messageManager->addSuccess(__('The store has been saved.'));
                $this->_getSession()->setMagedelightStorelocatorStorelocatorData(false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        '*/*/edit',
                        [
                        'store_parent_id' => $storelocator->getStoreParentId(),
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
                $this->messageManager->addException($e, __('Something went wrong while saving the store.'));
            }

            $this->_getSession()->setMagedelightStorelocatorStorelocatorData($data);
            $resultRedirect->setPath(
                '*/*/edit',
                [
                'store_parent_id' => $storelocator->getStoreParentId(),
                'store' => $switch_id,
                '_current' => true
                    ]
            );
            return $resultRedirect;
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Storepickup::storeinfo_save');
    }
}
