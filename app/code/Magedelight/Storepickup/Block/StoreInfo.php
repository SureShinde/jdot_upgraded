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


namespace Magedelight\Storepickup\Block;

use Magento\Sales\Model\Order\Address;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;

/**
 * Invoice view  comments form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class StoreInfo extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'storeinfo.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var AddressRenderer
     */
    protected $addressRenderer;

    /**
     * @param TemplateContext $context
     * @param Registry $registry
     * @param PaymentHelper $paymentHelper
     * @param AddressRenderer $addressRenderer
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        AddressRenderer $addressRenderer,
        array $data = []
    ) {
        $this->addressRenderer = $addressRenderer;
        $this->coreRegistry = $registry;
        $this->_isScopePrivate = true;
        parent::__construct($context, $data);
    }


    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * Returns string with formatted address
     *
     * @param Address $address
     * @return null|string
     */
    public function getStoreAddress($pickupstore)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $store = $objectManager->get('Magedelight\Storepickup\Model\Storelocator')->load($pickupstore);
        
        
        $pickupStorename = str_replace('\n', ', ', $store->getAddress());
        $pickupStorename .= '<br>'.$store->getCity();
        $pickupStorename .= '<br>'.$store->getState();
        $pickupStorename .= '<br>'.$this->getCountryName($store->getCountry());
        $pickupStorename .= '<br>'.$this->getContactInfo($store);

        return $pickupStorename;
    }

    /**
     *
     * @param string $countryval
     * @return string
     */
    public function getCountryName($countryval)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $countryOption = $objectManager->get('Magedelight\Storepickup\Model\Source\Country');
        $countryArray = $countryOption->getOptions();
        return $countryArray[$countryval];
    }

     /**
      *
      * @param type $storelocatorid
      * @return string
      */
    public function getContactInfo($store)
    {
        $phone = $store->getTelephone();
        $phone_frontend_status = $store->getPhoneFrontendStatus();
        $html = "";

        if ($phone_frontend_status) {
            if ($phone != '') {
                $html .= "<span>T:</span>";
                $televalue = explode(':', $phone);
                foreach ($televalue as $key => $singlevalue) {
                    $html .= ' '.$singlevalue.',';
                }
            }
        }
        return rtrim($html, ',');
    }
    
    /**
     *
     * @param int $pickupstore
     * @return string
     */
    public function getStoreName($pickupstore)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $store = $objectManager->get('Magedelight\Storepickup\Model\Storelocator')->load($pickupstore);
        
        $pickupStorename = $store->getStorename();
        return $pickupStorename;
    }
    
    /**
     *
     * @param object $order
     * @return Date
     */
    public function getFormatedDate($order)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $localeDate = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        return $formattedDate = $localeDate->formatDate(
            $localeDate->scopeDate(
                $order->getStore(),
                $order->getPickupDate(),
                true
            ),
            \IntlDateFormatter::MEDIUM,
            false
        );
    }
}
