<?php
namespace Infomodus\Dhllabel\Model\Config;

class Methodofpayment implements \Magento\Framework\Option\ArrayInterface
{
    protected $accountFactory;
    public function __construct(\Infomodus\Dhllabel\Model\AccountFactory $accountFactory)
    {
        $this->accountFactory = $accountFactory;
    }

    public function toOptionArray()
    {
        $c = array(
            array('label' => 'Shipper', 'value' => 'S'),
            /*array('label' => 'Recipient', 'value' => 'R'),*/
            /*array('label' => 'Third Party/Other', 'value' => 'T'),*/
        );
        $dhlAcctModel = $this->accountFactory->create()->getCollection();
        if (count($dhlAcctModel) > 0) {
            foreach ($dhlAcctModel as $u1) {
                $c[] = array('label' => $u1->getCompanyname(), 'value' => $u1->getId());
            }
        }
        return $c;
    }
}