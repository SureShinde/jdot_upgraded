<?php

namespace RLTSquare\PriceEditRole\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Backend\Model\Auth\Session;

class Data extends AbstractHelper
{
    protected $authSession;

    public function __construct(Context $context,Session $authSession)
    {
        parent::__construct($context);
        $this->authSession = $authSession;

    }

    const XML_PATH_HELLOWORLD = 'rltsquare_role/';

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    public function getGeneralConfig($code, $storeId = null)
    {

        return $this->getConfigValue(self::XML_PATH_HELLOWORLD .'general/'. $code, $storeId);
    }

    public function getSelectedUser()
    {
       $user = $this->getGeneralConfig('user');
       return $user;
    }
    public function getCurrentUser()
    {
       $currentUser = $this->authSession->getUser();
       $id = $currentUser->getId();
       return $id;
    }

}