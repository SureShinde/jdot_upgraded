<?php 
namespace Magecomp\Sms\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    // GENERAL Configuration
	const SMS_GENERAL_ENABLED = 'sms/general/enable';
	const SMS_GENERALSECTION_BUTTONCLASS = 'sms/generalsection/buttonclass';
    const SMS_GENERALSECTION_DEFAULTCOUNTRY = 'sms/generalsection/defaultcountry';
	const SMS_ADMIN_MOBILE = 'admintemplate/admingeneral/mobile';

	protected $_storeManager;

	public function __construct(
	\Magento\Framework\App\Helper\Context $context,
	\Magento\Store\Model\StoreManagerInterface $storeManager)
	{
		$this->_storeManager = $storeManager;
		parent::__construct($context);
	}

    public function getStoreid()
    {
        return $this->_storeManager->getStore()->getId();
    }

    public function getStoreName()
    {
        return $this->_storeManager->getStore()->getName();
    }

    public function getStoreUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

	public function isEnabled()
	{
        return $this->scopeConfig->getValue(self::SMS_GENERAL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreid());
    }

    public function getButtonclass()
    {
        return $this->scopeConfig->getValue(self::SMS_GENERALSECTION_BUTTONCLASS,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreid());
    }

    public function getDefaultcontry()
    {
        return $this->scopeConfig->getValue(self::SMS_GENERALSECTION_DEFAULTCOUNTRY,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreid());
    }

    public function checkAdminNumber()
    {
        return $this->scopeConfig->getValue(self::SMS_ADMIN_MOBILE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreid());
    }

    public function getAdminNumber()
    {
        if($this->isEnabled() && $this->checkAdminNumber() != '' && $this->checkAdminNumber() != null)
        {
            return $this->checkAdminNumber();
        }
    }
}