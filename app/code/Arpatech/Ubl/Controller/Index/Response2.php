<?php

namespace Arpatech\Ubl\Controller\Index;

class Response2 extends \Magento\Framework\App\Action\Action {

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_cartManagementInterface;
    protected $quoteFactory;
    protected $customerFactory;
    protected $customerRepository;
    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->quoteFactory = $quoteFactory;
        $this->_cartManagementInterface = $cartManagementInterface;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }


    public function execute() {
        $store = $this->storeManager->getStore(1);

        $quoteIds = explode(",",$_REQUEST['quoteIds']);
        $logArr = Array();
        foreach ($quoteIds as $value){
            try{
                $quote = $this->quoteFactory->create()->load($value);
                if($quote->getCustomerGroupId() == 0){

                    $customer=$this->customerFactory->create();
                    $customer->setWebsiteId(1);
                    $customer->loadByEmail($quote->getShippingAddress()->getEmail());
                    if(!$customer->getEntityId()) {
                        $customer->setWebsiteId(1);

                        //If not avilable then create this customer
                        $customer->setWebsiteId(1)
                            ->setStore($store)
                            ->setFirstname($quote->getBillingAddress()->getFirstname())
                            ->setLastname($quote->getBillingAddress()->getLastname())
                            ->setEmail($quote->getBillingAddress()->getEmail())
                            ->setCustomerIsGuest(1);

                        $customer->save();
                    }
                    $quote->assignCustomer($this->customerRepository->getById($customer->getId()));
                    $quote->save();
                }
                $this->_cartManagementInterface->placeOrderUBL($quote->getId());
                $logArr[] = $value . ' Imported';
            }
            catch (Exception $e) {
                $logArr[] = $value. ' '.$e->getMessage(). ' Failed';
            }
        }

        print_r($logArr);
    }
}
