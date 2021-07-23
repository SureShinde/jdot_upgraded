<?php
namespace Magecomp\Sms\Block\Customer;

class Update extends \Magento\Framework\View\Element\Template
{
    protected $helpercustomer;
    protected $customersession;

	public function __construct(
	    \Magento\Framework\View\Element\Template\Context $context,
        \Magecomp\Sms\Helper\Customer $helpercustomer,
        \Magento\Customer\Model\Session $customersession,
        array $data = [])
    {
        $this->helpercustomer = $helpercustomer;
        $this->customersession = $customersession;
        parent::__construct($context, $data);
    }

	public function getButtonclass()
	{
        return $this->helpercustomer->getButtonclass();
	}

	public function getCustomerMobile() {
        if($this->customersession->isLoggedIn())
        {
            return $this->customersession->getCustomer()->getMobilenumber();
        }
    }

    public function getDefaultContry()
    {
        return $this->helpercustomer->getDefaultcontry();
    }
}