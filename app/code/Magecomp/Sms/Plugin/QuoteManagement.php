<?php
namespace Magecomp\Sms\Plugin;

use Magento\Framework\Exception\LocalizedException;

class QuoteManagement
{
    protected $custsession;
    protected $helpercustomer;
    protected $checkoutSession;
    protected $quoteRepository;

    public function __construct(
        \Magento\Customer\Model\Session $custsession,
        \Magecomp\Sms\Helper\Customer $helpercustomer,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->custsession = $custsession;
        $this->helpercustomer = $helpercustomer;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    public function beforePlaceOrder(\Magento\Quote\Model\QuoteManagement $subject, $cartId, $paymentMethod = null)
    {
        if(!$this->custsession->isLoggedIn() && $this->helpercustomer->isOrderConfirmationForUser())
        {
            $quoteId = $this->checkoutSession->getQuote()->getId();
            if ($quoteId > 0)
            {
                $verifynumber =  $this->checkoutSession->getGuestOrderMobile();
                $quote = $this->quoteRepository->get($quoteId);
                $shippingNumber = $quote->getBillingAddress()->getTelephone();
                if($verifynumber != $shippingNumber)
                {
                    throw new LocalizedException(__('Please, Make sure you have entered '.$verifynumber.' as billing phone number.'));
                }
            }
        }
        return [$cartId, $paymentMethod];
    }
}
