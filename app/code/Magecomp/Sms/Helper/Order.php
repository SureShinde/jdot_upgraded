<?php 
namespace Magecomp\Sms\Helper;

use Magento\Store\Model\ScopeInterface;

class Order extends \Magecomp\Sms\Helper\Data
{
    // USER TEMPLATE
    const SMS_IS_CUSTOMER_ORDER_NOTIFICATION = 'usertemplate/userorderplace/enable';
    const SMS_CUSTOMER_ORDER_NOTIFICATION_TEMPLATE = 'usertemplate/userorderplace/template';

	//ADMIN TEMPLATE
    const SMS_IS_ADMIN_ORDER_NOTIFICATION = 'admintemplate/adminorderplace/enable';
    const SMS_ADMIN_ORDER_NOTIFICATION_TEMPLATE = 'admintemplate/adminorderplace/template';

	public function isOrderNotificationForUser() {
        return $this->isEnabled() && $this->scopeConfig->getValue(self::SMS_IS_CUSTOMER_ORDER_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreid());
    }

    public function getOrderNotificationUserTemplate()
    {
        if($this->isEnabled())
        {
            return  $this->scopeConfig->getValue(self::SMS_CUSTOMER_ORDER_NOTIFICATION_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreid());
        }
    }

    public function isOrderNotificationForAdmin()
    {
        return $this->isEnabled() && $this->scopeConfig->getValue(self::SMS_IS_ADMIN_ORDER_NOTIFICATION,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreid());
    }

    public function getOrderNotificationForAdminTemplate()
    {
        if($this->isEnabled())
        {
            return  $this->scopeConfig->getValue(self::SMS_ADMIN_ORDER_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreid());
        }
    }
}