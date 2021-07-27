<?php
namespace Magecomp\Sms\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Sms extends AbstractDb
{
    protected function _construct()
    {
    	$this->_init("sms_verify", "sms_verify_id");
    }
}
