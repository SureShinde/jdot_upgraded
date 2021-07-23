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

use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\State;
use Magedelight\Storepickup\Model\StorelocatorFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Router implements RouterInterface
{

    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Event manager
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * Store manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Author factory
     * @var \Magedelight\Storepickup\Model\StorelocatorFactory
     */
    protected $storelocatorFactory;

    /**
     * Config primary
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * Url
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * Response
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var bool
     */
    protected $dispatched;

    /**
     * @param ActionFactory $actionFactory
     * @param ManagerInterface $eventManager
     * @param UrlInterface $url
     * @param State $appState
     * @param StorelocatorFactory $storelocatorFactory
     * @param StoreManagerInterface $storeManager
     * @param ResponseInterface $response
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ActionFactory $actionFactory,
        ManagerInterface $eventManager,
        UrlInterface $url,
        State $appState,
        StorelocatorFactory $storelocatorFactory,
        StoreManagerInterface $storeManager,
        ResponseInterface $response,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->actionFactory = $actionFactory;
        $this->eventManager = $eventManager;
        $this->url = $url;
        $this->appState = $appState;
        $this->storelocatorFactory = $storelocatorFactory;
        $this->storeManager = $storeManager;
        $this->response = $response;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Validate and Match Storelocator Author and modify request
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     * //TODO: maybe remove this and use the url rewrite table.
     */
    public function match(RequestInterface $request)
    {

        if (!$this->dispatched) {
            $urlKey = trim($request->getPathInfo(), '/');
            $origUrlKey = $urlKey;
            /** @var Object $condition */
            $condition = new DataObject(['url_key' => $urlKey, 'continue' => true]);
            $urlKey = $condition->getUrlKey();

            if ($condition->getRedirectUrl()) {
                $this->response->setRedirect($condition->getRedirectUrl());
                $request->setDispatched(true);
                return $this->actionFactory->create('Magento\Framework\App\Action\Redirect', ['request' => $request]);
            }

            if (!$condition->getContinue()) {
                return null;
            }

            $entities = [
                'storelocator' => [
                    'list_url' => $this->scopeConfig->getValue(
                        'magedelight_storepickup/listviewinfo/frontend_url',
                        ScopeInterface::SCOPE_STORES
                    ),
                    'list_action' => 'index',
                    'factory' => $this->storelocatorFactory,
                    'controller' => 'index',
                    'action' => 'index',
                ]
            ];
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $suffix = trim($this->scopeConfig->getValue('magedelight_storepickup/listviewinfo/listpage_suffix', $storeScope), '/');
            $urlKey = str_replace('.' . $suffix, '', $urlKey);
            foreach ($entities as $entity => $settings) {
                if ($settings['list_url']) {
                    if ($urlKey == $settings['list_url']) {
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $customerSession = $objectManager->get('Magento\Customer\Model\Session');

                        $request->setModuleName('storepickup')
                                ->setControllerName($settings['controller'])
                                ->setActionName($settings['list_action']);
                        $urlKey .= (strlen($suffix) > 0 || $suffix != '') ? '.' . str_replace('.', '', $suffix) : '';
                        /* $urlKey = trim($request->getPathInfo(), '/'); */
                        $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $urlKey);
                        $this->dispatched = true;
                        return $this->actionFactory->create(
                            'Magento\Framework\App\Action\Forward',
                            ['request' => $request]
                        );
                    }
                }
            }
            
            
            /** @var \Magedelight\Storepickup\Model\Storelocator $instance */
            $instance = $this->storelocatorFactory->create();

            $id = $instance->checkUrlKey($urlKey, $this->storeManager->getStore()->getId());

            if (!$id) {
                return null;
            }

            $request->setModuleName('storepickup')
                    ->setControllerName('index')
                    ->setActionName('view')
                    ->setParam('id', $id);
            $urlKey .= (strlen($suffix) > 0 || $suffix != '') ? '.' . str_replace('.', '', $suffix) : '';

            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $urlKey);
            $request->setDispatched(true);
            $this->dispatched = true;
            return $this->actionFactory->create(
                'Magento\Framework\App\Action\Forward',
                ['request' => $request]
            );
        }
        return null;
    }
}
