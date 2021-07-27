<?php
namespace Magecomp\Sms\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;

class CheckoutLoadObserver implements ObserverInterface
{
    protected $messageManager;
    protected $_urlManager;
    protected $helpercustomer;
    protected $_responseFactory;
    protected $customersession;
    protected $checkoutSession;

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $urlManager,
        \Magecomp\Sms\Helper\Customer $helpercustomer,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Customer\Model\Session $customersession,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {

        $this->messageManager = $messageManager;
        $this->_urlManager = $urlManager;
        $this->helpercustomer = $helpercustomer;
        $this->_responseFactory = $responseFactory;
        $this->customersession = $customersession;
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try
        {
            if(!$this->helpercustomer->isOrderConfirmationForUser())
                return $this;

            if($this->customersession->isLoggedIn())
            {
                $mobile = $this->customersession->getCustomer()->getMobilenumber();
                if($mobile == '' || $mobile == null)
                {
                    $url = $this->_urlManager->getUrl('sms/customer/update', ['_secure' => true]);
                    $this->_responseFactory->create()->setRedirect($url)->sendResponse();
                    $this->setRefererUrl($url);
                }
                return $this;
            }
            else
            {
                $guestorderconfirm = $this->checkoutSession->getGuestOrderConfirm();
                if($guestorderconfirm)
                {
                    return $this;
                }
                else
                {
                    $url = $this->_urlManager->getUrl('sms/customer/checkout', ['_secure' => true]);
                    $this->_responseFactory->create()->setRedirect($url)->sendResponse();
                    $this->setRefererUrl($url);
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $url = $this->_urlManager->getUrl('*/*/', ['_secure' => true]);
        $this->_responseFactory->create()->setRedirect($url)->sendResponse();
        $this->setRefererUrl($url);
    }
}
