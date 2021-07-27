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

namespace Magedelight\Storepickup\Controller\Storeholiday;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Model\QuoteRepository;

class saveshipment extends Action
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     *
     * @param StoreholidayFactory $storeholidayFactory
     * @param PageFactory $pageFactory
     * @param Context $context
     */
    public function __construct(Context $context, QuoteRepository $quoteRepository, \Psr\Log\LoggerInterface $logger)
    {
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     *
     * @return Array
     */
    public function execute()
    {
        $quoteId = $this->getRequest()->getParam('QuoteID');
        $storeId = $this->getRequest()->getParam('StoreID');
        $pickupDate = ($this->getRequest()->getParam('PickupDate'))?$this->getRequest()->getParam('PickupDate'):'';
        //set pickup date
        if(!empty($storeId)){
            $this->setPickupCart($quoteId, $storeId);
        }
        
        //set pickup date
        if(!empty($pickupDate)){
            $this->setPickupDate($quoteId, $pickupDate);
        }
        
        $storelocatorData = array("status"=>"success","pickupDate"=>$pickupDate);
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($storelocatorData);
        return $resultJson;
    }
    
    public function setPickupCart($quoteId, $customData)
    {
        try {
            $quote = $this->quoteRepository->get($quoteId); // Get quote by id
            $quote->setData('pickup_store', $customData); // Fill data
            $this->quoteRepository->save($quote); // Save quote
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        
    }
    
    public function setPickupDate($quoteId, $customData)
    {
        try {
            $quote = $this->quoteRepository->get($quoteId); // Get quote by id
            $quote->setData('pickup_date', date("d-m-Y H:i", strtotime($customData))); // Fill data
            $this->quoteRepository->save($quote); // Save quote
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
