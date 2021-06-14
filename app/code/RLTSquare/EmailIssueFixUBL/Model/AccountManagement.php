<?php

namespace RLTSquare\EmailIssueFixUBL\Model;

class AccountManagement extends \Magento\Customer\Model\AccountManagement {



    public function isEmailAvailable($customerEmail, $websiteId = null) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager  = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $customerObj = $objectManager->create('\Magento\Customer\Model\Customer');
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        $shippingAddress = $cart->getQuote()->getShippingAddress();

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/guestFriendsFamily.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('------------');

        try {
            if ($shippingAddress) {
                $shippingAddress->setData('email', $customerEmail);
                $shippingAddress->save();
            }
        } catch (NoSuchEntityException $e) {
            $logger->info($e->getMessage());
        }

        try {
            if ($websiteId === null) {
                $websiteId = $storeManager->getStore()->getWebsiteId();
            }
            $customerObj->setWebsiteId($websiteId);
            $customerObj->loadByEmail($customerEmail);

            return ($customerObj->getId() == null)? true: false;
        } catch (NoSuchEntityException $e) {
            return true;
        }
    }

}
