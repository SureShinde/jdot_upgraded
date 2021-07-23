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

use Magedelight\Storepickup\Model\ResourceModel\Storelocator\Collection;
use Magento\Framework\Controller\ResultFactory;

class MassEnable extends \Magento\Backend\App\Action
{

    /**
     * Field id
     */
    const ID_FIELD = 'storelocator_id';

    /**
     * @var bool
     */
    protected $isActive = true;

    /**
     * Redirect url
     */
    const REDIRECT_URL = '*/*/';

    /**
     * Resource collection
     *
     * @var string
     */
    protected $collection = 'Magedelight\Storepickup\Model\ResourceModel\Storelocator\Collection';

    /**
     * Page model
     *
     * @var string
     */
    protected $model = 'Magedelight\Storepickup\Model\Storelocator';

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $selected = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded');
        $collection = $this->_objectManager->create($this->collection);
        try {
            if (!empty($excluded)) {
                $collection->addFieldToFilter(static::ID_FIELD, ['nin' => $excluded]);
                $this->massAction($collection);
            } elseif (!empty($selected)) {
                $collection->addFieldToFilter(static::ID_FIELD, ['in' => $selected]);
                $this->massAction($collection);
            } else {
                $this->messageManager->addError(__('Please select product(s).'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(static::REDIRECT_URL);
    }

    protected function massAction($collection)
    {
        $count = 0;
        foreach ($collection->getItems() as $storelocator) {
            $storelocator->setIsActive($this->isActive);
            $storelocator->save();
            ++$count;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been enable.', $count));
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Storepickup::storeinfo_save');
    }
}
