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

namespace Magedelight\Storepickup\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ActionFactory;
use Magedelight\Storepickup\Model\StorelocatorFactory;
use Magento\Customer\Model\Session;

abstract class Storelocator extends \Magento\Framework\App\Action\Action
{

    /**
     * Response
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;
    protected $pageFactory;
    protected $registry;
    protected $dispatched;
    protected $url;
    protected $_modelstorelocatorFactory;

    /**
     * @var Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     *
     * @param Context $context
     * @param ActionFactory $actionFactory
     * @param UrlInterface $url
     * @param StorelocatorFactory $modelStorelocatorFactory
     * @param ResponseInterface $response
     * @param PageFactory $pageFactory
     * @param Registry $registry
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $customerSession
     * @return type
     */
    public function __construct(
        Context $context,
        ActionFactory $actionFactory,
        StorelocatorFactory $modelStorelocatorFactory,
        PageFactory $pageFactory,
        Registry $registry,
        ScopeConfigInterface $scopeConfig,
        Session $customerSession
    ) {
        $this->pageFactory = $pageFactory;
        $this->actionFactory = $actionFactory;
        $this->registry = $registry;
        $this->url = $context->getUrl();
        $this->_modelstorelocatorFactory = $modelStorelocatorFactory;
        $this->response = $context->getResponse();
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        return parent::__construct($context);
    }

    public function dispatch(RequestInterface $request)
    {
        $storelocatorflag = $this->scopeConfig->getValue(
            'magedelight_storepickup/storeinfo/storepickupenable',
            ScopeInterface::SCOPE_STORES
        );
        if (!$storelocatorflag) {
            $this->_actionFlag->set('', 'no-dispatch', true);
            $this->getResponse()->setRedirect(
                $this->_url->getUrl('cms/noroute/index')
            );
        }

        $this->checkRedirect($request);
        return parent::dispatch($request);
    }

    /*
     * Check Guest customer access storelocator or not
     * return Redirect to customer 
     */

    protected function checkRedirect(RequestInterface $request)
    {
        $guestflag = $this->scopeConfig->getValue(
            'magedelight_storepickup/storeinfo/allowguestcustomer',
            ScopeInterface::SCOPE_STORES
        );
        // customer login action
        if (!$guestflag) {
            /* Check customer is login or not */
            if (!$this->customerSession->authenticate()) {
                $url = $this->url->getUrl('customer/account/login');
                $this->response->setRedirect($url);
                $request->setDispatched(true);
                return $this->actionFactory->create('Magento\Framework\App\Action\Redirect');
            }
        }
    }
}
