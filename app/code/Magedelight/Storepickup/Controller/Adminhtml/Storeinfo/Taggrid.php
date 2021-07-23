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
use Magedelight\Storepickup\Model\StorelocatorFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magedelight\Storepickup\Model\TagFactory;
use Magedelight\Storepickup\Helper\Data as Storehelper;

class Taggrid extends \Magedelight\Storepickup\Controller\Adminhtml\Tag
{

    protected $resultLayoutFactory;


/*public function __construct(
    \Magento\Backend\App\Action\Context $context,
    \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
    \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
) {
    parent::__construct($context, $productBuilder);
    $this->resultLayoutFactory = $resultLayoutFactory;
}*/

    public function __construct(
        Registry $registry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        TagFactory $tagFactory,
        Storehelper $storeHelper,
        Context $context
    ) {
        $this->backendSession = $context->getSession();
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($registry, $tagFactory, $context, $storeHelper);
    }




    public function execute()
    {

        try {
            //$this->productBuilder->build($this->getRequest());
            $resultLayout = $this->resultLayoutFactory->create();
            
            $request = $this->getRequest();
            $tagStores = $request->getPost('tag_ids', null);
            /*$this->productBuilder->build($request);*/
            
            $resultLayout->getLayout()->getBlock('magedelight.storepickup.edit.tab.taggrid')
                ->setTags($tagStores);
            return $resultLayout;
        } catch (\Exception $ex) {
            throw new \Magento\Framework\Exception\LocalizedException(__($ex->getMessage()));
        }
    }
}
