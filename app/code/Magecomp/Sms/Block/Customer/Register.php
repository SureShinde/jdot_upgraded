<?php
namespace Magecomp\Sms\Block\Customer;

class Register extends \Magento\Framework\View\Element\Template
{
    protected $helpercustomer;
	public function __construct(
	    \Magento\Framework\View\Element\Template\Context $context,
        \Magecomp\Sms\Helper\Customer $helpercustomer,
        array $data = [])
    {
        $this->helpercustomer = $helpercustomer;
        parent::__construct($context, $data);
    }

	public function getButtonclass()
	{
        return $this->helpercustomer->getButtonclass();
	}

	public function IsSignUpConfirmation() {
        return $this->helpercustomer->isSignUpConfirmationForUser();
    }

    public function getDefaultContry()
    {
        return $this->helpercustomer->getDefaultcontry();
    }
}