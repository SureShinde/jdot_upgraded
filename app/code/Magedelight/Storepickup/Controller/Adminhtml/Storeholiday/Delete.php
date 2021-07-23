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

use Magedelight\Storepickup\Controller\Adminhtml\Storeholiday;

class Delete extends Storeholiday
{

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('holiday_id');
        if ($id) {
            $name = "";
            try {
                /** @var \Magedelight\Storepickup\Model\Storelocator $storelocator */
                $storeholiday = $this->storeholidayFactory->create();
                $storeholiday->load($id);
                $name = $storeholiday->getName();
                $storeholiday->delete();

                $this->messageManager->addSuccess(__('The store has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_magedelight_storelocator_storeholiday_on_delete',
                    ['name' => $name, 'status' => 'success']
                );
                $resultRedirect->setPath('*/*/');

                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_magedelight_storelocator_storeholiday_on_delete',
                    ['name' => $name, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('*/*/edit', ['holiday_id' => $id]);
                return $resultRedirect;
            }
        }

        $this->_redirect('*/*/');
    }

    /**
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Storepickup::storeholiday_delete');
    }
}
