<?php 
namespace Magecomp\Sms\Helper;

use Magento\Store\Model\ScopeInterface;

class Invoice extends \Magecomp\Sms\Helper\Data
{
    // USER TEMPLATE
    const SMS_IS_CUSTOMER_INVOICE_NOTIFICATION = 'usertemplate/userinvoice/enable';
    const SMS_CUSTOMER_INVOICE_NOTIFICATION_TEMPLATE = 'usertemplate/userinvoice/template';

	//ADMIN TEMPLATE
    const SMS_IS_ADMIN_INVOICE_NOTIFICATION = 'admintemplate/admininvoice/enable';
    const SMS_ADMIN_INVOICE_NOTIFICATION_TEMPLATE = 'admintemplate/admininvoice/template';

	public function isInvoiceNotificationForUser() {
        return $this->isEnabled() && $this->scopeConfig->getValue(self::SMS_IS_CUSTOMER_INVOICE_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreid());
    }

    public function getInvoiceNotificationUserTemplate()
    {
        if($this->isEnabled())
        {
            return  $this->scopeConfig->getValue(self::SMS_CUSTOMER_INVOICE_NOTIFICATION_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreid());
        }
    }

    public function isInvoiceNotificationForAdmin()
    {
        return $this->isEnabled() && $this->scopeConfig->getValue(self::SMS_IS_ADMIN_INVOICE_NOTIFICATION,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreid());
    }

    public function getInvoiceNotificationForAdminTemplate()
    {
        if($this->isEnabled())
        {
            return  $this->scopeConfig->getValue(self::SMS_ADMIN_INVOICE_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreid());
        }
    }
}