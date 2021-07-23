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

namespace Magedelight\Storepickup\Controller\Index;

use Magedelight\Storepickup\Controller\Storelocator as StorelocatorController;
use Magento\Store\Model\ScopeInterface;

class Index extends StorelocatorController
{

    /**
     * Meta information
     */
    const META_DESCRIPTION_CONFIG_PATH = 'magedelight_storepickup/listviewinfo/meta_description';
    const META_Title_CONFIG_PATH = 'magedelight_storepickup/listviewinfo/meta_title';
    const META_KEYWORDS_CONFIG_PATH = 'magedelight_storepickup/listviewinfo/meta_keywords';

    /* Store Title */
    const XML_PATH_STORE_TITLE = 'magedelight_storepickup/listviewinfo/meta_title';

    public function execute()
    {
        $resultPage = $this->pageFactory->create();
        $title = $this->scopeConfig->getValue(self::META_Title_CONFIG_PATH, ScopeInterface::SCOPE_STORE);

        if (empty($title)) {
            $title = $this->scopeConfig->getValue(self::XML_PATH_STORE_TITLE, ScopeInterface::SCOPE_STORE);
        }

        if (empty($title)) {
            $title = 'Storelocator';
        }

        if ($title) {
            $resultPage->getConfig()->getTitle()->set($title);
        }
        $description = $this->scopeConfig->getValue(self::META_DESCRIPTION_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
        if ($description) {
            $resultPage->getConfig()->setDescription($description);
        }
        $keyword = $this->scopeConfig->getValue(self::META_KEYWORDS_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
        if ($keyword) {
            $resultPage->getConfig()->setKeywords($keyword);
        }
        
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
