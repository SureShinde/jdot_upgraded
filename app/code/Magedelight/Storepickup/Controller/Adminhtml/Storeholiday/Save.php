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

use Magento\Framework\Registry;
use Magedelight\Storepickup\Controller\Adminhtml\Storeholiday;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magedelight\Storepickup\Model\StoreholidayFactory;
use Magento\Backend\Model\Session;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\Helper\Js as JsHelper;
use Magedelight\Storepickup\Helper\Data as Storehelper;

class Save extends Storeholiday
{

    /**
     * storelocator factory
     * @var
     */
    protected $storeholidayFactory;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;

    /**
     *
     * @param JsHelper $jsHelper
     * @param Registry $registry
     * @param StoreholidayFactory $storeholidayFactory
     * @param Context $context
     */
    public function __construct(
        JsHelper $jsHelper,
        Registry $registry,
        StoreholidayFactory $storeholidayFactory,
        Context $context,
        Storehelper $storeHelper
    ) {
        $this->jsHelper = $jsHelper;
        parent::__construct($registry, $storeholidayFactory, $context, $storeHelper);
    }

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        //$data = $this->getRequest()->getPost();

        $data = $this->getRequest()->getPost('storeholiday');
        $tempdata = $data;

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($tempdata['all_store'] == 0) {
            $data["holiday_applied_stores"] = $tempdata['all_store'];
        } else {
            $data["holiday_applied_stores"] = implode(',', $tempdata['holiday_applied_stores']);
        }

        if (!array_key_exists('is_repetitive', $tempdata)) {
            /* array_push($data,"is_repetitive",0); */
            $data["is_repetitive"] = 0;
        }

        if ($data) {
            $defaultstoreid = $this->storeHelper->_getDefaultStoreId();
            $holidayId = isset($data['holiday_parent_id']) ? (int)$data['holiday_parent_id'] : null;
            
            $md_store_id = isset($data['store_id']) ? $data['store_id'] : null;
            $switch_id = isset($data['store_id']) ? $data['store_id'] : $defaultstoreid;
            
            if ($defaultstoreid != $md_store_id && isset($holidayId)) {
                $storeholiday = $this->storeholidayFactory
                                     ->create()
                                     ->getCollection()
                                     ->addFieldToFilter('holiday_parent_id', $holidayId)
                                     ->addFieldToFilter('store_id', $md_store_id)
                                     ->getFirstItem();
                $sholidayid = $storeholiday['holiday_id'];
                if (isset($sholidayid)) {
                    $storeholiday->load($sholidayid);
                    $storeholiday->setData($data);
                    $storeholiday->setHolidayId($sholidayid);
                } else {
                    $data['holiday_id'] = null;
                    $storeholiday->setData($data);
                    $storeholiday->setStoreId($md_store_id);
                    $storeholiday->setHolidayParentId($holidayId);
                }
            } else {
                $storeholiday = $this->initStoreholiday();
                $data['holiday_parent_id'] = $holidayId;
                $storeholiday->setStoreId($defaultstoreid);
                $storeholiday->setHolidayParentId(null);
                $storeholiday->setData($data);
            }
            
            try {
                $storeholiday->save();
                if ($holidayId == null) {
                    $saved_id = $storeholiday->getHolidayId();
                    $storeholiday->setHolidayParentId($saved_id);
                    $storeholiday->save();
                }
                $this->messageManager->addSuccess(__('The Holiday has been saved.'));
                $this->_getSession()->setMagedelightStorelocatorStoreholidayData(false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        '*/*/edit',
                        [
                        'holiday_parent_id' => $storeholiday->getHolidayParentId(),
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

            $this->_getSession()->setMagedelightStorelocatorStoreholidayData($data);
            $resultRedirect->setPath(
                '*/*/edit',
                [
                'holiday_parent_id' => $storeholiday->getHolidayParentId(),
                'store' => $switch_id,
                '_current' => true
                    ]
            );
            return $resultRedirect;
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Storepickup::storeholiday_save');
    }
}
